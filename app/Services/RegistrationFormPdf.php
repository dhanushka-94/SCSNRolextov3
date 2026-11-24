<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * The paper registration form is the same for everyone, so it is built once with a
 * browser engine (correct Sinhala and Tamil shaping) and served as a static file.
 * DomPDF is only the fallback when that file has not been built yet.
 */
class RegistrationFormPdf
{
    public const DOWNLOAD_NAME = 'SPPNR-planter-registration-form.pdf';

    public static function builtPath(): string
    {
        return public_path('forms/SPPNR-planter-registration-form.pdf');
    }

    public static function isBuilt(): bool
    {
        return is_file(self::builtPath());
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(bool $browser = false): array
    {
        $path = fn (string $value): string => str_replace('\\', '/', $value);
        // Browsers need file:// URLs for local files; DomPDF wants plain absolute paths.
        $url = fn (string $value): string => $browser ? 'file:///'.ltrim($path($value), '/') : $path($value);

        // Browser builds use the current official Noto Sinhala (full shaping tables); DomPDF keeps its own copy.
        $sinhalaDirectory = $browser && is_file(storage_path('fonts/shaping/NotoSansSinhala-Regular.ttf')) ? 'fonts/shaping' : 'fonts';
        $sinhalaRegular = storage_path($sinhalaDirectory.'/NotoSansSinhala-Regular.ttf');
        $sinhalaBold = is_file(storage_path($sinhalaDirectory.'/NotoSansSinhala-Bold.ttf')) ? storage_path($sinhalaDirectory.'/NotoSansSinhala-Bold.ttf') : $sinhalaRegular;
        $tamilRegular = storage_path('fonts/NotoSansTamil-Regular.ttf');
        $tamilBold = is_file(storage_path('fonts/NotoSansTamil-Bold.ttf')) ? storage_path('fonts/NotoSansTamil-Bold.ttf') : $tamilRegular;
        $hasTamil = is_file($tamilRegular);

        return [
            'browser' => $browser,
            'logo' => $url(public_path('SPPNR-logo.png')),
            'fontRegular' => $url($sinhalaRegular),
            'fontBold' => $url($sinhalaBold),
            // DomPDF has no per-glyph fallback, so Tamil text gets its own font family.
            'fontTamilRegular' => $hasTamil ? $url($tamilRegular) : null,
            'fontTamilBold' => $hasTamil ? $url($tamilBold) : null,
            'governingBodies' => collect(config('app.governing_bodies'))->map(function (array $body) use ($url) {
                $body['logo_path'] = $url(public_path($body['logo']));

                return $body;
            })->all(),
        ];
    }

    /**
     * Render the form with Chrome or Edge and store it as the static download.
     */
    public function build(): string
    {
        $browser = $this->findBrowser();

        if ($browser === null) {
            throw new RuntimeException('No Chrome or Edge found. Set PDF_BROWSER in .env to the browser executable.');
        }

        $html = view('planter.pdf.registration-form', self::viewData(browser: true))->render();

        $directory = dirname(self::builtPath());
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $htmlFile = storage_path('app/registration-form-print.html');
        file_put_contents($htmlFile, $html);

        $output = self::builtPath();
        $result = Process::timeout(120)->run([
            $browser,
            '--headless=new',
            '--disable-gpu',
            '--no-pdf-header-footer',
            '--allow-file-access-from-files',
            '--user-data-dir='.storage_path('app/pdf-browser-profile'),
            '--print-to-pdf='.$output,
            'file:///'.ltrim(str_replace('\\', '/', $htmlFile), '/'),
        ]);

        @unlink($htmlFile);

        if (! is_file($output) || filesize($output) === 0) {
            throw new RuntimeException('The browser did not produce a PDF: '.trim($result->errorOutput() ?: $result->output()));
        }

        return $output;
    }

    private function findBrowser(): ?string
    {
        $candidates = array_filter([
            env('PDF_BROWSER'),
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
