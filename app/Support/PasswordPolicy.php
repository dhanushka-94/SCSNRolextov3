<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

/**
 * One password rule for every place a password is set: web admin, planter web, mobile API,
 * user management and planter registry. Keep it here so the rule and the docs never drift.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH = 10;

    public static function rule(): Password
    {
        return Password::min(self::MIN_LENGTH)->letters()->mixedCase()->numbers();
    }

    public static function description(): string
    {
        return 'At least '.self::MIN_LENGTH.' characters, with upper and lower case letters and a number.';
    }
}
