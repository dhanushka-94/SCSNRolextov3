<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Services\NotifySmsService;
use Illuminate\Http\JsonResponse;

class SmsTemplateController extends ApiController
{
    /** Developer only (route is guarded by api.staff:developer). */
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
}
