<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\PasswordPolicy;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user('web');
        $target = $this->route('user');

        return $actor?->isAdmin() === true
            && ($target === null || $target->canBeManagedBy($actor));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            // A Developer keeps the Developer role on their own account; others cannot assign it.
            'role' => ['required', Rule::in(array_keys(User::assignableRoles($this->user('web'))))],
            'status' => ['required', Rule::in(array_keys(User::statuses()))],
            'password' => ['nullable', 'confirmed', PasswordPolicy::rule()],
        ];
    }
}
