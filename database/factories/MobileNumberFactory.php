<?php

namespace Javaabu\MobileVerification\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * @extends Factory<MobileNumber>
 */
class MobileNumberFactory extends Factory
{
    protected $model = MobileNumber::class;

    public function modelName(): string
    {
        return MobileVerification::mobileNumberModel();
    }

    public function definition(): array
    {
        return [
            'country_code' => MobileVerification::defaultCountryCode(),
            'number' => $this->faker->unique()->numerify($this->faker->randomElement(['7######', '9######'])),
            'user_type' => 'user',
        ];
    }

    /**
     * Owned by a user, which verified it
     */
    public function forUser(Model $user): static
    {
        return $this->state(fn () => [
            'user_type' => $user->getMorphClass(),
        ])->afterMaking(function (MobileNumber $mobile_number) use ($user) {
            $mobile_number->user()->associate($user);
        });
    }

    /**
     * Without an owner, for a kind of user (its morph class)
     */
    public function forUserType(string $morph_class): static
    {
        return $this->state(fn () => ['user_type' => $morph_class]);
    }

    /**
     * Locked after too many wrong codes
     */
    public function locked(): static
    {
        return $this->state(fn () => [
            'attempts' => (int) MobileVerification::config('max_attempts', 5),
        ])->afterMaking(function (MobileNumber $mobile_number) {
            $mobile_number->forceFill(['verification_code_created_at' => now()]);
        });
    }
}
