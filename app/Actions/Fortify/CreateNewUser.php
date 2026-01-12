<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'teacher', // Automatically assign Faculty/Teacher role
            ]);

            // Split name into first and last name
            $parts = explode(' ', $input['name'], 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';

            // Create Teacher profile
            \App\Models\Teacher::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP' . str_pad((string) $user->id, 5, '0', STR_PAD_LEFT), // Simple generation
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $input['email'],
                'status' => 'active',
            ]);

            return $user;
        });
    }
}
