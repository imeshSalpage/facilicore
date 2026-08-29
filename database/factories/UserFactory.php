<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'                => fake()->name(),
            'email'               => fake()->unique()->safeEmail(),
            'role'                => 'end_user',
            'registration_number' => 'REG-' . fake()->unique()->numberBetween(10000, 99999),
            'department'          => 'Main Department',
            'phone'               => fake()->phoneNumber(),
            'approval_status'     => 'approved',
            'approved_at'         => now(),
            'email_verified_at'   => now(),
            'password'            => static::$password ??= Hash::make('password'),
            'remember_token'      => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_at'     => null,
            'approved_by'     => null,
        ]);
    }

    /**
     * Indicate that the user registration was rejected.
     */
    public function rejected(string $reason = 'Invalid registration details'): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status'  => 'rejected',
            'rejection_reason' => $reason,
            'approved_at'      => null,
            'approved_by'      => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
