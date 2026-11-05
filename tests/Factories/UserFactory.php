<?php

namespace Javaabu\MobileVerification\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => 'އަޙްމަދު ޢަލީ',
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'status' => UserStatuses::APPROVED,
        ];
    }

    /**
     * With a verified mobile number
     */
    public function withMobileNumber(string $number = '7771234', string $country_code = '960'): static
    {
        return $this->afterCreating(function (User $user) use ($number, $country_code) {
            MobileNumber::factory()->forUser($user)->create([
                'number' => $number,
                'country_code' => $country_code,
            ]);
        });
    }

    public function withTwoFactor(string $secret = 'JBSWY3DPEHPK3PXP'): static
    {
        return $this->state([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function banned(): static
    {
        return $this->state(['status' => UserStatuses::BANNED]);
    }
}
