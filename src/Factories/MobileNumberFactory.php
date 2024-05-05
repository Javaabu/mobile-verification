<?php

namespace Javaabu\MobileVerification\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Activitylog\CauserTypes;
use Javaabu\MobileVerification\Models\MobileNumber;

class MobileNumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MobileNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => $this->faker->regexify('(9|7)[0-9]{6}'),
            'country_code' => $this->faker->randomElement(config('mobile-verification.allowed_country_codes')),
            'user_type' => $this->faker->randomElement(CauserTypes::getKeys())
        ];
    }
}
