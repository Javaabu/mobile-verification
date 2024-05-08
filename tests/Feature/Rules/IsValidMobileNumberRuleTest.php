<?php

namespace Javaabu\MobileVerification\Tests\Feature\Rules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class IsValidMobileNumberRuleTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    // it can validate number format for maldivian numbers
    public function it_can_validate_number_format_for_maldivian_numbers()
    {
        $rule = new IsValidMobileNumber('user');
        $value = $this->checkRule($rule, 'number', '7771234');
        $this->assertTrue($value);

        $rule = new IsValidMobileNumber('user');
        $value = $this->checkRule($rule, 'number', '9771234');
        $this->assertTrue($value);

        $rule = new IsValidMobileNumber('user');
        $value = $this->checkRule($rule, 'number', '97712345555');
        $this->assertFalse($value);

        $rule = new IsValidMobileNumber('user', Countries::SaudiArabia->getCountryCode());
        $value = $this->checkRule($rule, 'number', '977123455555');
        $this->assertTrue($value);
    }

    /** @test */
    public function it_can_validate_that_no_other_user_of_same_type_has_the_number()
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'country_code' => Countries::Maldives->getCountryCode(),
            'number' => '7825222',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $rule = new IsValidMobileNumber('user', can_be_taken_by_user: false);
        $value = $this->checkRule($rule, 'number', '7825222');
        $this->assertFalse($value);
    }

    /** @test */
    public function it_can_validate_if_the_number_regardless_is_used_by_another_user_if_the_option_is_checked()
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'country_code' => Countries::Maldives->getCountryCode(),
            'number' => '7825222',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $rule = new IsValidMobileNumber('user', can_be_taken_by_user: true);
        $value = $this->checkRule($rule, 'number', '7825222');
        $this->assertTrue($value);
    }

    /** @test */
    // it can validate if otp can be sent to the number
    public function it_can_validate_if_otp_can_be_sent_to_the_number()
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'country_code' => Countries::Maldives->getCountryCode(),
            'number' => '7825222',
            'user_type' => 'user',
            'user_id' => $user->id,
            'attempts' => 0,
        ]);

        $rule = new IsValidMobileNumber('user', can_be_taken_by_user: true, can_send_otp: true);
        $value = $this->checkRule($rule, 'number', '7825222');
        $this->assertTrue($value);

        $user_two = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'country_code' => Countries::Maldives->getCountryCode(),
            'number' => '7326655',
            'user_type' => 'user',
            'user_id' => $user_two->id,
            'attempts' => config('mobile-verification.max_attempts'),
        ]);

        $rule = new IsValidMobileNumber('user', can_be_taken_by_user: true, can_send_otp: true);
        $value = $this->checkRule($rule, 'number', '7326655');
        $this->assertFalse($value);
    }
}
