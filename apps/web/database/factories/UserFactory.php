<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->numerify('##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => UserStatusEnum::ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    public function lecturer(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('code', UserRoleEnum::LECTURER->value)->first();

            if ($role) {
                $user->roles()->attach($role->id);
            }
        });
    }

    public function student(array $overrides = []): static
    {
        return $this->state(fn (array $attributes) => $overrides)
                ->afterCreating(function (User $user) {
                    $role = Role::where('code', UserRoleEnum::STUDENT->value)->first();

                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                });
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role_id' => UserRoleEnum::ADMIN->value,
            'password' => Hash::make('admin123'), // default password
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
