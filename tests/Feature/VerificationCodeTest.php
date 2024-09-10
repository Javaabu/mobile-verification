<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Support\VerificationCodeGenerator;

class VerificationCodeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_request_verification_code()
    {
        $this->withoutExceptionHandling();
        $user = $this->getUserWithMobileNumber();


        $this->assertDatabaseHas('mobile_numbers', [
            'number'            => '7528222',
            'country_code'      => '960',
            'user_id'           => $user->id,
            'user_type'         => 'user',
            'verification_code' => null,
        ]);

        $this->postJson('/api/mobile-verifications/verification-code', [
            'user_type' => 'user',
            'number'    => '7528222',
        ]);

        $mobile_number = $user->phone()->first();
        $this->assertNotNull($mobile_number->verification_code);
    }

    public function test_too_many_invalid_attempts_will_lock_the_mobile_number()
    {
        $user = User::factory()->create();
        MobileNumber::unguard();

        $mobile_number = MobileNumber::create([
            'number'       => '7528222',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        $this->mock(VerificationCodeGenerator::class, function ($mock) use ($mobile_number) {
            $mock->shouldReceive('handle')
                 ->andReturn('123456');
        });

        $response = $this->postJson('/api/mobile-verifications/verification-code', [
            'user_type' => 'user',
            'number'    => '7528222',
        ]);

        $verification_code_id = $response->json()['verification_code_id'];
        $max_attempts = config('mobile-verification.max_attempts');
        $try = 1;

        foreach (range(1, $max_attempts + 20) as $attempt) {
            $this->postJson('/api/mobile-verifications/verify', [
                'user_type'            => 'user',
                'number'               => '7528222',
                'verification_code'    => '999999',
                'verification_code_id' => $verification_code_id,
            ]);

            $mobile_number->refresh();
            $this->assertEquals($mobile_number->attempts, $try);

            if ($max_attempts == $try) {
                $mobile_number->refresh();
                $this->assertEquals($mobile_number->is_locked, true);
            }

            $try++;
        }

        $this->postJson('/api/mobile-verifications/verify', [
            'user_type'            => 'user',
            'number'               => '7528222',
            'verification_code'    => '999999',
            'verification_code_id' => $verification_code_id,
        ])
             ->assertJsonValidationErrors(['verification_code']);

        $this->postJson('/api/mobile-verifications/verification-code', [
            'user_type' => 'user',
            'number'    => '7528222',
        ])
            ->assertJsonValidationErrors(['number']);
    }

    public function getUserWithMobileNumber(string $number = '7528222'): User
    {
        $user = User::factory()->create();
        MobileNumber::unguard();

        $mobile_number = MobileNumber::create([
            'number'       => $number,
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        return $user;
    }
}
