<?php

namespace Javaabu\MobileVerification\Tests\Feature\ValidationRules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class MobileNumberValidationRuleTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    // it can validate country code
    public function it_can_validate_country_code()
    {
        $this->post('validate', [
            'country_code' => 'MV'
        ]);
    }

    /** @test */
    // it can validate number is required
    public function it_can_validate_number_is_required()
    {
        $this->post('validate', [
            'number' => ''
        ])->assertSessionHas('number', trans('mobile-verification::strings.validation.number.required', ['attribute' => 'number']));
    }


    /** @test
     * @throws \JsonException
     */
    public function it_can_validate_maldivian_mobile_numbers(): void
    {
        $this->post('validate', [
            'number' => '7326655'
        ])->assertSessionHasNoErrors();

        $this->post('validate', [
            'number' => '9326655'
        ])->assertSessionHasNoErrors();


        $this->post('/validate', [
            'number' => '3326655'
        ])->assertSessionHas('number', trans('mobile-verification::strings.validation.number.invalid', ['attribute' => 'number']));
    }

    /** @test */
    public function it_can_validate_foreign_mobile_numbers(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_can_validate_if_the_mobile_number_is_already_in_use_by_another_user_of_the_same_type(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_can_validate_if_the_mobile_number_is_already_in_use_by_another_user_of_a_different_type(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_can_validate_if_the_mobile_number_is_already_in_use_by_another_user_with_a_different_country_code(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_can_validate_country_codes(): void
    {
        $this->assertFalse(true);
    }
}
