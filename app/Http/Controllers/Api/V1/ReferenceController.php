<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AuditChecklistItem;
use App\Models\District;
use App\Models\Planter;
use App\Models\PlanterAuditChecklistAnswer;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;

class ReferenceController extends ApiController
{
    /** Public app details, governing bodies and whether maintenance is on. */
    public function meta(): JsonResponse
    {
        return response()->json([
            'app' => [
                'name' => config('app.name'),
                'short_name' => config('app.short_name'),
                'full_name' => config('app.full_name'),
                'version' => config('changelog.releases.0.version'),
                'copyright' => config('app.copyright'),
                'powered_by' => config('app.powered_by'),
            ],
            'governing_bodies' => collect(config('app.governing_bodies', []))
                ->map(fn (array $body) => [
                    'name' => $body['name'],
                    'short' => $body['short'] ?? $body['name'],
                    'logo_url' => asset($body['logo']),
                ])
                ->values(),
            'maintenance' => [
                'enabled' => SystemSetting::maintenanceEnabled(),
                'message' => SystemSetting::maintenanceMessage(),
            ],
        ]);
    }

    /** Districts with their Rubber Development Officer divisions, for registration forms. */
    public function locations(): JsonResponse
    {
        $districts = District::query()
            ->active()
            ->orderBy('name')
            ->with(['rdoDivisions' => fn ($query) => $query->active()->orderBy('name')])
            ->get();

        return response()->json([
            'districts' => $districts->map(fn (District $district) => [
                'id' => $district->id,
                'name' => $district->name,
                'code' => $district->code,
                'rdo_divisions' => $district->rdoDivisions->map(fn ($division) => [
                    'id' => $division->id,
                    'name' => $division->name,
                    'code' => $division->code,
                ])->values(),
            ])->values(),
        ]);
    }

    /** Dropdown values and labels, so the app never hard-codes them. */
    public function options(): JsonResponse
    {
        return response()->json([
            'business_types' => collect(Planter::businessTypes())->map(fn (array $label, string $value) => [
                'value' => $value,
                'label' => $label['en'],
                'label_si' => $label['si'],
            ])->values(),
            'process_plan_options' => collect(Planter::processPlanOptions())->map(fn (array $label, string $value) => [
                'value' => $value,
                'label' => $label['en'],
                'label_si' => $label['si'],
            ])->values(),
            'registration_types' => $this->pairs(Planter::registrationTypes()),
            'planter_statuses' => $this->pairs(Planter::statuses()),
            'audit_result_outcomes' => $this->pairs(Planter::auditResultOutcomes()),
            'checklist_results' => $this->pairs(PlanterAuditChecklistAnswer::results()),
            'checklist_modules' => $this->pairs(AuditChecklistItem::modules()),
        ]);
    }

    /**
     * @param  array<string, string>  $map
     * @return list<array{value: string, label: string}>
     */
    private function pairs(array $map): array
    {
        return collect($map)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
