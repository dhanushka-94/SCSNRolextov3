<?php

namespace App\Console\Commands;

use App\Services\RegistrationFormPdf;
use Illuminate\Console\Command;
use Throwable;

class BuildRegistrationForm extends Command
{
    protected $signature = 'sppnr:build-registration-form';

    protected $description = 'Build the printable trilingual (Sinhala, English, Tamil) registration form PDF with Chrome or Edge';

    public function handle(RegistrationFormPdf $pdf): int
    {
        try {
            $path = $pdf->build();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Built '.$path.' ('.number_format(filesize($path) / 1024).' KB)');

        return self::SUCCESS;
    }
}
