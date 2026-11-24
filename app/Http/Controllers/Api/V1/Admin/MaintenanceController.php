<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends ApiController
{
    public function show(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = $request->user()->id;

        SystemSetting::write(
            SystemSetting::MAINTENANCE_MESSAGE,
            filled($data['message'] ?? null) ? trim($data['message']) : null,
            $userId,
        );
        SystemSetting::write(SystemSetting::MAINTENANCE_MODE, $data['enabled'] ? '1' : '0', $userId);

        ActivityLog::record('maintenance.updated', null, ['enabled' => (bool) $data['enabled']]);

        return response()->json($this->payload());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'enabled' => SystemSetting::maintenanceEnabled(),
            'message' => SystemSetting::maintenanceMessage(),
        ];
    }
}
