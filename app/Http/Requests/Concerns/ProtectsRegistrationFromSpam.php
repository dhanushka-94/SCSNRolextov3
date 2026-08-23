<?php

namespace App\Http\Requests\Concerns;

use App\Services\RegistrationSpamGuard;

trait ProtectsRegistrationFromSpam
{
    protected function prepareSpamProtection(): void
    {
        app(RegistrationSpamGuard::class)->assertNotSpam($this);
    }
}
