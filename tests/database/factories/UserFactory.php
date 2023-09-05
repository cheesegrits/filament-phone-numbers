<?php

namespace Cheesegrits\FilamentPhoneNumbers\Tests\Database\Factories;

use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'normalized_phone' => null,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function e164(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'phone' => $this->faker->e164PhoneNumber(),
            ];
        });
    }

    public function phone(string $phone): Factory
    {
        return $this->state(function (array $attributes) use ($phone) {
            return [
                'phone' => $phone,
            ];
        });
    }

    public function invalidPhone(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'phone' => substr($this->faker->phoneNumber(), 0, -2),
            ];
        });
    }

    public function nationalPhone(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'phone' => $this->faker->numerify('(###) ###-####'),
            ];
        });
    }
}
