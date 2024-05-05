<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class VerifyMobileNumberAvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_verify_mobile_number_availability_and_sends_correct_message_if_the_mobile_number_is_unavailable()
    {
        $user = User::factory()->create();
        $mobileNumber = MobileNumber::factory()->create([
            'user_id' => $user->id,
            'user_type' => 'user',
            'number' => '1234567890',
        ]);

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '1234567890',
            'user_id' => $user->id,
            'user_type' => 'user',
        ]);

        $this->post('verify', [
            'mobile_number' => '1234567890'
        ])
            ->assertSessionHas('mobile_number', 'The mobile number is already in use.');
    }

}
