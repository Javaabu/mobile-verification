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
    public function it_can_validate_maldivian_mobile_numbers(): void
    {

        $this->assertFalse(true);
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
