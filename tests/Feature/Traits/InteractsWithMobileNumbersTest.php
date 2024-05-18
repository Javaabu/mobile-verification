<?php

namespace Javaabu\MobileVerification\Tests\Feature\Traits;

use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class InteractsWithMobileNumbersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_to_activity_log_when_mobile_number_is_updated(): void
    {
        $this->assertDatabaseCount('activity_log', 0);

        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'number' => '7528222',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $new_mobile_number = MobileNumber::factory()->create([
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => null,
        ]);

        $this->actingAs($user);

        $this->post(route('update-mobile-number'), [
            'number' => '7326655',
            'token' => $new_mobile_number->generateVerificationCode(),
        ])
             ->assertSessionHasNoErrors()
             ->assertRedirect();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'updated',
            'subject_type' => 'mobile_number',
            'subject_id' => $mobile_number->id,
            'causer_type' => 'user',
            'causer_id' => $user->id,
            'properties' => json_encode([
                'attributes' => [
                    'user_id' => null,
                ],
                'old' => [
                    'user_id' => $user->id,
                ],
            ]),
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'updated',
            'subject_type' => 'mobile_number',
            'subject_id' => $new_mobile_number->id,
            'causer_type' => 'user',
            'causer_id' => $user->id,
            'properties' => json_encode([
                'attributes' => [
                    'user_id' => $user->id,
                ],
                'old' => [
                    'user_id' => null,
                ],
            ]),
        ]);
    }

    /** @test */
    public function it_deletes_the_associated_mobile_number_when_the_user_is_deleted(): void
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'number' => '7528222',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('mobile_numbers', 1);

        $user->delete();
        $this->assertDatabaseCount('mobile_numbers', 1);

        $user->forceDelete();
        $this->assertDatabaseCount('mobile_numbers', 0);
    }
}
