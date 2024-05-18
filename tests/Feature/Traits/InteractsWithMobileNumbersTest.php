<?php

namespace Javaabu\MobileVerification\Tests\Feature\Traits;

use Javaabu\Activitylog\Models\Activity;
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

        $this->patch('mobile-verification/update', [
            'number' => '7326655',
            'verification_code' => $new_mobile_number->generateVerificationCode(),
            'verification_code_id' => $new_mobile_number->verification_code_id->toString(),
        ])
             ->assertSessionHasNoErrors()
             ->assertRedirect();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $activity = Activity::where('description', 'updated')
            ->where('subject_type', 'mobile_number')
            ->where('subject_id', $new_mobile_number->id)
            ->where('causer_type', 'user')
            ->where('causer_id', $user->id)
            ->where('properties->attributes->user_id', $user->id)
            ->first();

        $this->assertNotNull($activity);
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
