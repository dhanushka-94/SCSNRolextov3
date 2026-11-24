<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'sppnr:create-admin
        {email : Sign-in email for the account}
        {--name= : Full name (asked when left out)}
        {--phone= : Phone number}
        {--role=developer : developer (Super Admin) or admin}
        {--reset-password : Set a new password for an account that already exists}';

    protected $description = 'Create the first Super Admin or Administrator without demo data. The password is asked for and never stored in history.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $role = (string) $this->option('role');

        if (! in_array($role, [User::ROLE_DEVELOPER, User::ROLE_ADMIN], true)) {
            $this->error('The role must be "developer" or "admin".');

            return self::FAILURE;
        }

        if (Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:190']])->fails()) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing && ! $this->option('reset-password')) {
            $this->error("{$email} already exists. Use --reset-password to set a new password.");

            return self::FAILURE;
        }

        $name = trim((string) ($this->option('name') ?: ($existing?->name ?? '')));

        if ($name === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Pass --name when running without prompts.');

                return self::FAILURE;
            }

            $name = trim((string) $this->ask('Full name'));
        }

        if ($name === '') {
            $this->error('A name is required.');

            return self::FAILURE;
        }

        if (! $this->input->isInteractive()) {
            $this->error('This command asks for the password, so it cannot run without prompts.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password ('.PasswordPolicy::description().')');
        $confirmation = (string) $this->secret('Confirm password');

        if ($password !== $confirmation) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $check = Validator::make(['password' => $password], ['password' => ['required', PasswordPolicy::rule()]]);

        if ($check->fails()) {
            $this->error($check->errors()->first('password'));

            return self::FAILURE;
        }

        if ($existing) {
            $existing->forceFill(['password' => $password, 'status' => User::STATUS_ACTIVE])->save();
            $existing->tokens()->delete();
            $this->info("Password reset for {$email}. Their signed-in devices were signed out.");

            return self::SUCCESS;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $this->option('phone') ?: null,
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $this->info("Created {$email} as ".User::roles()[$role].'.');

        return self::SUCCESS;
    }
}
