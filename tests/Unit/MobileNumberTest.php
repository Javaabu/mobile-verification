<?php

namespace Javaabu\MobileVerification\Tests\Unit;

use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MobileNumberTest extends TestCase
{
    #[Test]
    public function it_normalizes_numbers_and_country_codes(): void
    {
        $mobile_number = MobileNumber::factory()->create(['number' => '077 712-34', 'country_code' => '+960']);

        $this->assertSame('7771234', $mobile_number->number);
        $this->assertSame('960', $mobile_number->country_code);
        $this->assertSame('+9607771234', $mobile_number->formatted_number);
        $this->assertSame('7771234', $mobile_number->short_formatted_number);
        $this->assertSame('+9607771234', $mobile_number->routeNotificationForSms());
    }

    #[Test]
    public function other_country_codes_keep_the_prefix_in_the_short_number(): void
    {
        config()->set('mobile-verification.allowed_country_codes', ['960', '94']);

        $mobile_number = MobileNumber::factory()->create(['number' => '771234567', 'country_code' => '94']);

        $this->assertSame('+94771234567', $mobile_number->short_formatted_number);
        $this->assertSame(['960' => '+960', '94' => '+94'], MobileVerification::countryCodeOptions());
    }

    #[Test]
    public function it_hashes_codes_and_checks_them(): void
    {
        $mobile_number = MobileNumber::factory()->create();

        $code = $mobile_number->generateVerificationCode();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertNotSame($code, $mobile_number->getAttributes()['verification_code']);
        $this->assertNotNull($mobile_number->verification_code_id);
        $this->assertArrayNotHasKey('verification_code', $mobile_number->toArray());

        $this->assertFalse($mobile_number->checkVerificationCode($code === '000000' ? '111111' : '000000'));
        $this->assertSame(1, $mobile_number->fresh()->attempts);

        $this->assertTrue($mobile_number->checkVerificationCode($code));
    }

    #[Test]
    public function codes_expire(): void
    {
        $mobile_number = MobileNumber::factory()->create();
        $mobile_number->generateVerificationCode();

        $this->assertFalse($mobile_number->isVerificationCodeExpired());
        $this->assertSame(600, $mobile_number->verificationCodeExpiresIn());

        $this->travel(10)->minutes();

        $this->assertTrue($mobile_number->isVerificationCodeExpired());
        $this->assertSame(0, $mobile_number->verificationCodeExpiresIn());
    }

    #[Test]
    public function a_new_code_can_be_sent_after_the_resend_interval(): void
    {
        $mobile_number = MobileNumber::factory()->create();

        $this->assertTrue($mobile_number->canRequestCode());

        $mobile_number->generateVerificationCode();

        $this->assertTrue($mobile_number->wasSentRecently());
        $this->assertSame(30, $mobile_number->resendVerificationCodeIn());

        $this->travel(31)->seconds();

        $this->assertFalse($mobile_number->wasSentRecently());
        $this->assertTrue($mobile_number->canRequestCode());
    }

    #[Test]
    public function too_many_wrong_codes_lock_the_number_for_a_while(): void
    {
        $mobile_number = MobileNumber::factory()->create();
        $mobile_number->generateVerificationCode();

        foreach (range(1, 5) as $attempt) {
            $mobile_number->checkVerificationCode('wrong');
        }

        $this->assertTrue($mobile_number->isLocked());
        $this->assertSame(0, $mobile_number->remainingAttempts());
        $this->assertSame(30 * 60, $mobile_number->lockExpiresIn());
        $this->assertFalse($mobile_number->canRequestCode());

        $this->travel(30)->minutes();

        $this->assertFalse($mobile_number->isLocked());

        $mobile_number->resetAttempts();

        $this->assertSame(0, $mobile_number->attempts);
        $this->assertFalse($mobile_number->hasVerificationCode());
    }

    #[Test]
    public function the_code_length_is_configurable(): void
    {
        config()->set('mobile-verification.code_length', 4);

        $this->assertMatchesRegularExpression('/^\d{4}$/', MobileNumber::factory()->create()->generateVerificationCode());
    }

    #[Test]
    public function it_finds_numbers_as_users_write_them(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->assertTrue(MobileNumber::query()->hasNumber('7771234')->exists());
        $this->assertTrue(MobileNumber::query()->hasNumber('+9607771234')->exists());
        $this->assertTrue(MobileNumber::query()->search('777')->exists());
        $this->assertTrue(MobileNumber::query()->search('+960777')->exists());
        $this->assertFalse(MobileNumber::query()->search('999')->exists());

        $this->assertTrue(MobileVerification::find('user', null, '7771234')->user->is($user));
        $this->assertNull(MobileVerification::find('admin', null, '7771234'));
    }

    #[Test]
    public function the_same_number_can_belong_to_a_user_and_an_admin(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();
        $admin = Admin::factory()->withMobileNumber('7771234')->create();

        $this->assertSame('+9607771234', $user->formatted_mobile_number);
        $this->assertSame('+9607771234', $admin->formatted_mobile_number);
        $this->assertSame(2, MobileNumber::query()->count());
    }

    #[Test]
    public function the_response_data_describes_the_code_without_revealing_it(): void
    {
        $mobile_number = MobileNumber::factory()->create();
        $code = $mobile_number->generateVerificationCode();

        $data = $mobile_number->verificationCodeResponseData();

        $this->assertSame($mobile_number->verification_code_id, $data['verification_code_id']);
        $this->assertFalse($data['is_registered']);
        $this->assertSame(600, $data['expires_in']);
        $this->assertSame(30, $data['resend_in']);
        $this->assertNotContains($code, $data);
    }
}
