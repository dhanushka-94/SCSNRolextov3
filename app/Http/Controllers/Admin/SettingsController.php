<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $mode = SystemSetting::query()->where('key', SystemSetting::MAINTENANCE_MODE)->with('updater')->first();

        return view('admin.settings.index', [
            'maintenanceOn' => SystemSetting::maintenanceEnabled(),
            'maintenanceMessage' => SystemSetting::maintenanceMessage(),
            'maintenanceChangedAt' => $mode?->updated_at,
            'maintenanceChangedBy' => $mode?->updater?->name,
        ]);
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        abort_unless($request->user('web')?->canManageSettings(), 403);

        $validated = $request->validate([
            'maintenance_mode' => ['required', 'in:0,1'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = $request->user('web')->id;

        SystemSetting::write(SystemSetting::MAINTENANCE_MESSAGE, filled($validated['maintenance_message'] ?? null)
            ? trim($validated['maintenance_message'])
            : null, $userId);
        SystemSetting::write(SystemSetting::MAINTENANCE_MODE, $validated['maintenance_mode'], $userId);

        ActivityLog::record('maintenance.updated', null, ['enabled' => $validated['maintenance_mode'] === '1']);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', $validated['maintenance_mode'] === '1'
                ? 'Maintenance mode is ON. Only administrators can use the system.'
                : 'Maintenance mode is OFF. The system is available to everyone.');
    }
}
