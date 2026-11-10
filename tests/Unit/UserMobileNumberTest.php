<?php

namespace Javaabu\MobileVerification\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Javaabu\MobileVerification\Events\MobileNumberUpdated;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserMobileNumberTest extends TestCase
{
    #[Test]
    public function users_have_one_verified_number(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->assertTrue($user->hasVerifiedMobile());
        $this->assertTrue($user->phone_verified);
        $this->assertFalse($user->needs_phone_verification);
        $this->assertSame('7771234', $user->mobile_number);
        $this->assertSame('960', $user->mobile_number_country_code);
        $this->assertSame('7771234', $user->short_mobile_number);
        $this->assertSame('+9607771234', $user->routeNotificationForSms());
        $this->assertSame('+9607771234', $user->routeNotificationForDhiraagu());

        $this->assertTrue(User::query()->phoneVerified()->whereKey($user->getKey())->exists());
        $this->assertTrue(User::query()->hasMobileNumber('7771234')->exists());
        $this->assertTrue(User::query()->hasShortMobileNumber('+9607771234')->exists());
        $this->assertTrue(User::query()->hasPhone('777')->exists());
        $this->assertTrue(User::findByMobileNumber('07771234')->is($user));
        $this->assertNull(Admin::findByMobileNumber('7771234'));
    }

    #[Test]
    public function users_without_a_number_need_to_verify_one(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasVerifiedMobile());
        $this->assertTrue($user->needs_phone_verification);
        $this->assertNull($user->formatted_mobile_number);
        $this->assertNull($user->routeNotificationForSms());
        $this->assertTrue(User::query()->phoneUnverified()->whereKey($user->getKey())->exists());
    }

    #[Test]
    public function updating_the_number_replaces_the_old_one_and_fires_an_event(): void
    {
        Event::fake([MobileNumberUpdated::class]);

        $user = User::factory()->withMobileNumber('7771234')->create();
        $admin = Admin::factory()->create();

        $this->assertNotNull($user->updateMobileNumber('9991234', causer: $admin));

        $this->assertSame('+9609991234', $user->formatted_mobile_number);
        $this->assertNull(MobileNumber::query()->hasNumber('7771234')->first()->user_id);

        Event::assertDispatched(MobileNumberUpdated::class, fn (MobileNumberUpdated $event) => $event->old_number === '+9607771234'
            && $event->new_number === '+9609991234'
            && $event->user->is($user)
            && $event->causer->is($admin));
    }

    #[Test]
    public function a_number_of_another_user_is_not_taken(): void
    {
        User::factory()->withMobileNumber('7771234')->create();
        $user = User::factory()->create();

        $this->assertNull($user->updateMobileNumber('7771234'));
        $this->assertFalse($user->fresh()->hasVerifiedMobile());
    }

    #[Test]
    public function removing_the_number(): void
    {
        Event::fake([MobileNumberUpdated::class]);

        $user = User::factory()->withMobileNumber('7771234')->create();

        $user->removeMobileNumber();

        $this->assertFalse($user->hasVerifiedMobile());
        $this->assertSame(1, MobileNumber::query()->count());

        Event::assertDispatched(MobileNumberUpdated::class, fn (MobileNumberUpdated $event) => $event->new_number === null);
    }

    #[Test]
    public function force_deleting_a_user_deletes_the_number_but_soft_deleting_keeps_it(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $user->delete();

        $this->assertSame(1, MobileNumber::query()->count());

        $user->forceDelete();

        $this->assertSame(0, MobileNumber::query()->count());
    }
}
