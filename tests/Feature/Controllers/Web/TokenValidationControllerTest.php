<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;

class TokenValidationControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function it_can_validate_the_token(): void
    {
        $this->withoutExceptionHandling();
        $mobile_number = MobileNumber::factory()
                                     ->create([
                                         'country_code' => '960',
                                         'number' => '7528222',
                                         'user_type' => 'user',
                                     ]);

        $token = $mobile_number->generateVerificationCode();

        $this->post('/verify', [
            'number' => '7528222',
            'token' => $token,
        ])
             ->assertSessionHasNoErrors()
             ->assertRedirect();
    }

    /** @test */
    public function it_can_validate_if_the_token_is_expired(): void
    {
        $this->withoutExceptionHandling();
        $mobile_number = MobileNumber::factory()
                                     ->create([
                                         'country_code' => '960',
                                         'number' => '7528222',
                                         'user_type' => 'user',
                                     ]);

        $token = $mobile_number->generateVerificationCode();

        $this->travelTo(now()->addMinutes(config('mobile-verification.verification_code_validity') + 1));

        $this->post('/verify', [
            'number' => '7528222',
            'token' => $token,
        ])
             ->assertSessionHasErrors('token');
    }

    /** @test */
    public function it_can_validate_if_the_token_is_invalid(): void
    {
        $this->withoutExceptionHandling();
        $mobile_number = MobileNumber::factory()
                                     ->create([
                                         'country_code' => '960',
                                         'number' => '7528222',
                                         'user_type' => 'user',
                                     ]);

        $mobile_number->generateVerificationCode();

        $this->travelTo(now()->addMinutes(config('mobile-verification.verification_code_validity') + 1));

        $this->post('/verify', [
            'number' => '7528222',
            'token' => '545454545',
        ])
             ->assertSessionHasErrors('token');
    }


}
