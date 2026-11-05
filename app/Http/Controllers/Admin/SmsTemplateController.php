<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotifySmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsTemplateController extends Controller
{
    public function index(NotifySmsService $sms): View
    {
        $templates = collect($sms->templates())->map(function (array $template, string $key) use ($sms) {
            return [
                'key' => $key,
                'name' => $template['name'] ?? $key,
                'trigger' => $template['trigger'] ?? '',
                'status' => $template['status'] ?? 'planned',
                'body' => $template['body'] ?? '',
                'sample' => $sms->renderSample($key),
                'placeholders' => $template['placeholders'] ?? [],
                'length' => mb_strlen((string) ($template['body'] ?? '')),
            ];
        })->values();

        return view('admin.sms-templates.index', [
            'templates' => $templates,
            'gatewayEnabled' => $sms->enabled(),
            'senderId' => config('services.notify.sender_id'),
        ]);
    }

    public function sendSamples(Request $request, NotifySmsService $sms): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'template' => ['nullable', 'string', 'max:80'],
        ]);

        $phone = $validated['phone'];
        $templateKey = $validated['template'] ?? null;

        if (filled($templateKey)) {
            $ok = $sms->sendSample($templateKey, $phone);

            return back()->with(
                $ok ? 'success' : 'error',
                $ok
                    ? "Sample SMS for “{$templateKey}” sent to {$phone}."
                    : "Could not send sample for “{$templateKey}”. Check logs and Notify.lk settings."
            );
        }

        $result = $sms->sendAllSamples($phone);
        $sentCount = count($result['sent']);
        $failedCount = count($result['failed']);

        if ($sentCount > 0 && $failedCount === 0) {
            return back()->with('success', "Sent {$sentCount} sample SMS message(s) to {$phone}.");
        }

        if ($sentCount > 0) {
            return back()->with('success', "Sent {$sentCount} sample(s); failed: ".implode(', ', $result['failed']).'.');
        }

        return back()->with('error', 'No sample SMS messages were sent. Check Notify.lk settings and logs.');
    }
}
