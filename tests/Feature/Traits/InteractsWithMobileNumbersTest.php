<?php

namespace Javaabu\MobileVerification\Tests\Feature\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class InteractsWithMobileNumbersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_update_the_mobile_number_of_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->updateMobileNumber('7528222', '91');

        $this->assertDatabaseHas('mobile_numbers', [
            'user_type' => User::class,
            'user_id' => $user->id,
            'country_code' => '91',
            'number' => '7528222',
            'attempts' => 0,
            'token_created_at' => null,
            'token' => null,
        ]);
    }

    /** @test */
    public function it_resets_the_tokens_and_attempts_when_updating_the_mobile_number(): void
    {
        $now = now();

        $phone = MobileNumber::factory()->create([
            'number' => '7528222',
            'country_code' => '91',
            'attempts' => 2,
            'token_created_at' => now(),
            'token' => '451146',
            'user_type' => User::class,
        ]);

        $phone->refresh();
        $this->assertTrue(Hash::check('451146', $phone->token));

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7528222',
            'country_code' => '91',
            'attempts' => 2,
            'token_created_at' => $now->toDateTimeString(),
            'user_type' => User::class,
        ]);

        /** @var User $user */
        $user = User::factory()->create();

        $user->updateMobileNumber('7528222', '91');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'user_type' => User::class,
            'user_id' => $user->id,
            'country_code' => '91',
            'number' => '7528222',
            'attempts' => 0,
            'token_created_at' => null,
            'token' => null,
        ]);
    }

    /** @test */
    public function it_logs_to_activity_log_when_mobile_number_is_updated(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_deletes_the_associated_mobile_number_when_the_user_is_deleted(): void
    {
        $this->assertFalse(true);
    }
}
