<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Services\NotifySmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(NotifySmsService $sms): View
    {
        $mode = SystemSetting::query()->where('key', SystemSetting::MAINTENANCE_MODE)->with('updater')->first();

        $smsTemplates = collect($sms->templates())->map(function (array $template, string $key) use ($sms) {
            $bodies = [];
            foreach (NotifySmsService::LANGUAGES as $lang) {
                $body = $sms->localised($template, 'body', $lang) ?? '';
                $bodies[$lang] = ['text' => $body, 'length' => mb_strlen($body)];
            }

            return [
                'key' => $key,
                'name' => [
                    'en' => $sms->templateName($key, 'en'),
                    'si' => $sms->templateName($key, 'si'),
                    'ta' => $sms->templateName($key, 'ta'),
                ],
                'trigger' => $template['trigger'] ?? '',
                'status' => $template['status'] ?? 'planned',
                'placeholders' => $template['placeholders'] ?? [],
                'bodies' => $bodies,
            ];
        })->values();

        return view('admin.settings.index', [
            'maintenanceOn' => SystemSetting::maintenanceEnabled(),
            'maintenanceMessage' => SystemSetting::maintenanceMessage(),
            'maintenanceChangedAt' => $mode?->updated_at,
            'maintenanceChangedBy' => $mode?->updater?->name,
            'smsTemplates' => $smsTemplates,
            'smsGatewayEnabled' => $sms->enabled(),
            'smsSenderId' => config('services.notify.sender_id'),
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

    public function sendSmsSamples(Request $request, NotifySmsService $sms): RedirectResponse
    {
        abort_unless($request->user('web')?->canManageSettings(), 403);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'language' => ['required', 'in:en,si,ta,all'],
            'template' => ['nullable', 'string', 'max:80'],
        ]);

        $phone = $validated['phone'];
        $languages = $validated['language'] === 'all'
            ? NotifySmsService::LANGUAGES
            : [$validated['language']];
        $templateKey = $validated['template'] ?? null;

        if (filled($templateKey)) {
            if (! array_key_exists($templateKey, $sms->templates())) {
                return back()->withInput()->with('error', 'That SMS template does not exist.');
            }

            $sent = 0;
            foreach ($languages as $lang) {
                if ($sms->sendSample($templateKey, $phone, $lang)) {
                    $sent++;
                }
            }

            return back()->withInput()->with(
                $sent > 0 ? 'success' : 'error',
                $sent > 0
                    ? "Sent {$sent} sample SMS message(s) for “{$templateKey}” to {$phone}."
                    : "Could not send the sample for “{$templateKey}”. Check the logs and Notify.lk settings."
            );
        }

        $result = $sms->sendAllSamples($phone, $languages);
        $sentCount = count($result['sent']);
        $failedCount = count($result['failed']);

        if ($sentCount > 0 && $failedCount === 0) {
            return back()->withInput()->with('success', "Sent {$sentCount} sample SMS message(s) to {$phone}.");
        }

        if ($sentCount > 0) {
            return back()->withInput()->with('success', "Sent {$sentCount} sample(s). Failed: ".implode(', ', $result['failed']).'.');
        }

        return back()->withInput()->with('error', 'No sample SMS messages were sent. Check Notify.lk settings and the logs.');
    }
}
