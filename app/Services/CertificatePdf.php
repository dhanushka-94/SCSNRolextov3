<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificatePdf
{
    public function download(Certificate $certificate, string $qrPng): BinaryFileResponse
    {
        $certificate->loadMissing(['planter.rdoDivision']);

        $html = view('certificates.print', [
            'certificate' => $certificate,
            'qrUrl' => 'data:image/png;base64,'.base64_encode($qrPng),
            'embedImages' => true,
            'forPdf' => true,
        ])->render();

        $directory = storage_path('app/certificate-pdfs');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $token = $certificate->id.'-'.uniqid();
        $htmlFile = $directory.DIRECTORY_SEPARATOR.$token.'.html';
        $pdfFile = $directory.DIRECTORY_SEPARATOR.$token.'.pdf';
        file_put_contents($htmlFile, $html);

        $browser = $this->findBrowser();
        $profile = storage_path('app/certificate-pdf-profiles/'.uniqid());

        if ($browser === null) {
            @unlink($htmlFile);
            throw new RuntimeException('No Chrome or Edge found to build the certificate PDF.');
        }

        if (! is_dir($profile)) {
            mkdir($profile, 0755, true);
        }

        $result = Process::timeout(90)->run([
            $browser,
            '--headless=new',
            '--disable-gpu',
            '--no-first-run',
            '--no-pdf-header-footer',
            '--allow-file-access-from-files',
            '--user-data-dir='.$profile,
            '--print-to-pdf='.$pdfFile,
            'file:///'.ltrim(str_replace('\\', '/', $htmlFile), '/'),
        ]);

        @unlink($htmlFile);

        if (! is_file($pdfFile) || filesize($pdfFile) === 0) {
            $detail = trim($result->errorOutput() ?: $result->output());

            throw new RuntimeException('The browser did not produce a certificate PDF: '.mb_substr($detail, 0, 400));
        }

        $name = str_replace('/', '-', $certificate->certificate_number).'.pdf';

        return response()->download($pdfFile, $name, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    private function findBrowser(): ?string
    {
        $candidates = array_filter([
            env('PDF_BROWSER'),
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
