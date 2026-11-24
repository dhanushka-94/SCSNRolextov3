<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Services\NotifySmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsTemplateController extends ApiController
{
    public function index(NotifySmsService $sms): JsonResponse
    {
        $templates = collect($sms->templates())->map(fn (array $template, string $key) => [
            'key' => $key,
            'name' => $template['name'] ?? $key,
            'trigger' => $template['trigger'] ?? '',
            'status' => $template['status'] ?? 'planned',
            'body' => $template['body'] ?? '',
            'sample' => $sms->renderSample($key),
            'placeholders' => $template['placeholders'] ?? [],
            'length' => mb_strlen((string) ($template['body'] ?? '')),
        ])->values();

        return response()->json([
            'templates' => $templates,
            'gateway_enabled' => $sms->enabled(),
            'sender_id' => config('services.notify.sender_id'),
        ]);
    }

    public function sendSamples(Request $request, NotifySmsService $sms): JsonResponse
    {
        abort_unless($request->user()?->canSendSmsSamples(), 403);

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'template' => ['nullable', 'string', 'max:80'],
        ]);

        if (filled($data['template'] ?? null)) {
            $ok = $sms->sendSample($data['template'], $data['phone']);

            return response()->json(
                ['message' => $ok ? 'Sample SMS sent.' : 'Could not send the sample. Check the gateway settings.'],
                $ok ? 200 : 422,
            );
        }

        $result = $sms->sendAllSamples($data['phone']);

        return response()->json([
            'sent' => $result['sent'],
            'failed' => $result['failed'],
        ], count($result['failed']) && ! count($result['sent']) ? 422 : 200);
    }
}
