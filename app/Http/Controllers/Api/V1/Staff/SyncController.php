<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Audits\CompletePlanterAuditRequest;
use App\Http\Requests\Audits\SavePlanterAuditChecklistRequest;
use App\Http\Requests\Planters\StorePlanterRequest;
use App\Http\Requests\Planters\UpdatePlanterRequest;
use App\Models\ApiSyncOperation;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Models\User;
use App\Services\PlanterAuditService;
use App\Services\PlanterRegistryService;
use App\Support\ApiRouteParams;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Offline queue upload. The app sends a batch of operations it recorded while offline.
 *
 * Each operation is applied on its own. The response has one result per operation:
 *   applied  – saved (replayed=true when this client_id was already applied before)
 *   rejected – invalid or not allowed; do not resend unchanged, fix the data first
 *   failed   – server error; safe to resend the same client_id later
 */
class SyncController extends ApiController
{
    public function store(Request $request, PlanterAuditService $audits, PlanterRegistryService $registry): JsonResponse
    {
        $data = $request->validate([
            'operations' => ['required', 'array', 'min:1', 'max:100'],
            'operations.*.client_id' => ['required', 'string', 'max:120'],
            'operations.*.type' => ['required', 'string', 'max:40'],
            'operations.*.payload' => ['sometimes', 'array'],
        ]);

        $user = $request->user();
        $results = [];

        foreach ($data['operations'] as $operation) {
            $results[] = $this->runOperation($user, $operation, $audits, $registry);
        }

        return response()->json(['results' => $results]);
    }

    /**
     * @param  array{client_id: string, type: string, payload?: array<string, mixed>}  $operation
     * @return array<string, mixed>
     */
    private function runOperation(User $user, array $operation, PlanterAuditService $audits, PlanterRegistryService $registry): array
    {
        $clientId = $operation['client_id'];
        $type = $operation['type'];

        $stored = ApiSyncOperation::query()
            ->where('actor_type', ApiSyncOperation::ACTOR_STAFF)
            ->where('actor_id', $user->id)
            ->where('client_id', $clientId)
            ->first();

        if ($stored) {
            return ['client_id' => $clientId, 'type' => $stored->type, 'status' => 'applied', 'replayed' => true, 'data' => $stored->response];
        }

        try {
            $data = $this->apply($user, $type, $operation['payload'] ?? [], $audits, $registry);

            ApiSyncOperation::query()->create([
                'actor_type' => ApiSyncOperation::ACTOR_STAFF,
                'actor_id' => $user->id,
                'client_id' => $clientId,
                'type' => $type,
                'response' => $data,
            ]);

            return ['client_id' => $clientId, 'type' => $type, 'status' => 'applied', 'replayed' => false, 'data' => $data];
        } catch (ValidationException $exception) {
            return ['client_id' => $clientId, 'type' => $type, 'status' => 'rejected', 'errors' => $exception->errors()];
        } catch (ModelNotFoundException) {
            return ['client_id' => $clientId, 'type' => $type, 'status' => 'rejected', 'message' => 'The record no longer exists.'];
        } catch (AuthorizationException|HttpException $exception) {
            return ['client_id' => $clientId, 'type' => $type, 'status' => 'rejected', 'message' => $exception->getMessage() ?: 'You are not allowed to do this.'];
        } catch (InvalidArgumentException $exception) {
            return ['client_id' => $clientId, 'type' => $type, 'status' => 'rejected', 'message' => $exception->getMessage()];
        } catch (Throwable $exception) {
            Log::error('Offline sync operation failed', ['client_id' => $clientId, 'type' => $type, 'error' => $exception->getMessage()]);

            return ['client_id' => $clientId, 'type' => $type, 'status' => 'failed', 'message' => 'Server error. Retry this operation later.'];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function apply(User $user, string $type, array $payload, PlanterAuditService $audits, PlanterRegistryService $registry): array
    {
        return match ($type) {
            'planter.create' => $this->createPlanter($user, $payload, $registry),
            'planter.update' => $this->updatePlanter($user, $payload, $registry),
            'audit.start' => $this->auditAction($user, $payload, fn (PlanterAudit $audit) => $audits->start($audit, $user)),
            'audit.checklist' => $this->saveChecklist($user, $payload, $audits),
            'audit.submit' => $this->auditAction($user, $payload, fn (PlanterAudit $audit) => $audits->submitForReview($audit, $user)),
            'audit.complete' => $this->completeAudit($user, $payload, $audits),
            default => throw new InvalidArgumentException("Unknown operation type [{$type}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function createPlanter(User $user, array $payload, PlanterRegistryService $registry): array
    {
        abort_unless($user->canManagePlanterRecords(), 403, 'You cannot register planters.');

        $data = $this->validatedWith(StorePlanterRequest::class, $this->syncRequest($user, $payload));
        $planter = $registry->createFromStaff($data, $user);

        return $this->planterSummary($planter);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function updatePlanter(User $user, array $payload, PlanterRegistryService $registry): array
    {
        abort_unless($user->canManagePlanterRecords(), 403, 'You cannot edit planters.');

        $planter = Planter::query()->findOrFail($payload['planter_id'] ?? null);
        $data = $this->validatedWith(UpdatePlanterRequest::class, $this->syncRequest($user, $payload, ['planter' => $planter]));
        $planter = $registry->updateFromStaff($planter, $data, $user);

        return $this->planterSummary($planter);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveChecklist(User $user, array $payload, PlanterAuditService $audits): array
    {
        $audit = $this->findAudit($user, $payload);
        $data = $this->validatedWith(SavePlanterAuditChecklistRequest::class, $this->syncRequest($user, $payload, ['audit' => $audit]));

        $audits->saveChecklistAnswers($audit, $user, $data['answers'] ?? []);

        return $this->auditSummary($audit->fresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function completeAudit(User $user, array $payload, PlanterAuditService $audits): array
    {
        $audit = $this->findAudit($user, $payload);
        $data = $this->validatedWith(CompletePlanterAuditRequest::class, $this->syncRequest($user, $payload, ['audit' => $audit]));

        $audit = $audits->completeRound(
            $audit,
            $user,
            $data['status'],
            $data['outcome_notes'] ?? null,
            $data['signature'] ?? null,
        );

        return $this->auditSummary($audit);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  callable(PlanterAudit): mixed  $action
     * @return array<string, mixed>
     */
    private function auditAction(User $user, array $payload, callable $action): array
    {
        $audit = $this->findAudit($user, $payload);
        $action($audit);

        return $this->auditSummary($audit->fresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function findAudit(User $user, array $payload): PlanterAudit
    {
        $audit = PlanterAudit::query()->findOrFail($payload['audit_id'] ?? null);

        abort_unless($user->canWorkAuditRound($audit->round), 403, 'You cannot work on this audit round.');

        return $audit;
    }

    /**
     * Builds a request that carries the operation payload, the signed-in staff user and the
     * route parameters (for example the audit or planter), so the web FormRequests run unchanged.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $route
     */
    private function syncRequest(User $user, array $payload, array $route = []): Request
    {
        $request = Request::create('/', 'POST', $payload);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => new ApiRouteParams($route));

        return $request;
    }

    /**
     * Validates one operation with its own payload bound as the current request. Some shared rules
     * (for example "RDO division belongs to the district") read the global request() helper, which
     * would otherwise see the outer sync request instead of this operation's fields.
     *
     * @param  class-string<\Illuminate\Foundation\Http\FormRequest>  $formClass
     * @return array<string, mixed>
     */
    private function validatedWith(string $formClass, Request $operationRequest): array
    {
        $outer = app('request');
        app()->instance('request', $operationRequest);

        try {
            return $this->validatedFrom($formClass, $operationRequest);
        } finally {
            app()->instance('request', $outer);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function planterSummary(Planter $planter): array
    {
        return [
            'planter_id' => $planter->id,
            'temporary_id' => $planter->temporary_id,
            'identification_number' => $planter->identification_number,
            'status' => $planter->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function auditSummary(PlanterAudit $audit): array
    {
        return [
            'audit_id' => $audit->id,
            'planter_id' => $audit->planter_id,
            'round' => $audit->round,
            'status' => $audit->status,
            'is_complete' => $audit->isComplete(),
        ];
    }
}
