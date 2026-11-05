<?php

namespace Javaabu\MobileVerification\Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Support\NumberFormatValidator;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MobileVerificationTest extends TestCase
{
    #[Test]
    public function login_can_be_turned_on_in_code(): void
    {
        UserTypes::register('vendor', ['model' => User::class, 'guard' => 'web']);

        $this->assertFalse(MobileVerification::enabledFor('vendor'));

        MobileVerification::enableFor('vendor');

        $this->assertTrue(MobileVerification::enabledFor('vendor'));
        $this->assertTrue(MobileVerification::enabledFor(UserTypes::get('user')));
    }

    #[Test]
    public function registration_needs_the_type_feature_and_the_config(): void
    {
        $this->assertTrue(MobileVerification::allowsRegistration(UserTypes::get('user')));
        $this->assertFalse(MobileVerification::allowsRegistration(UserTypes::get('admin')));

        config()->set('mobile-verification.registration', false);

        $this->assertFalse(MobileVerification::allowsRegistration(UserTypes::get('user')));
    }

    #[Test]
    public function it_normalizes_input(): void
    {
        $this->assertSame('7771234', MobileVerification::normalizeNumber(' 0777-1234 '));
        $this->assertSame('', MobileVerification::normalizeNumber(null));
        $this->assertSame('960', MobileVerification::normalizeCountryCode('+960'));
        $this->assertSame('960', MobileVerification::normalizeCountryCode('00960'));
        $this->assertSame('960', MobileVerification::normalizeCountryCode(''));
        $this->assertSame('', MobileVerification::normalizeCountryCode('', false));
        $this->assertSame('+960', MobileVerification::numberPrefix() . MobileVerification::defaultCountryCode());
        $this->assertSame((new Admin)->getMorphClass(), MobileVerification::morphClassFor('admin'));
    }

    #[Test]
    public function the_default_number_format(): void
    {
        $format = new NumberFormatValidator;

        $this->assertTrue($format->passes('960', '7771234'));
        $this->assertTrue($format->passes('960', '9991234'));
        $this->assertFalse($format->passes('960', '3301234'));
        $this->assertFalse($format->passes('960', '77712345'));
        $this->assertTrue($format->passes('94', '771234567'));
        $this->assertFalse($format->passes('94', '12'));
        $this->assertFalse($format->passes('94', '1234567890123456'));
    }

    #[Test]
    public function the_rule_checks_registration_per_type(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $validate = fn ($rule, array $data = ['number' => '7771234']) => Validator::make($data, ['number' => [$rule]])->passes();

        $this->assertTrue($validate((new IsValidMobileNumber('user'))->registered()));
        $this->assertFalse($validate((new IsValidMobileNumber('admin'))->registered()));
        $this->assertFalse($validate((new IsValidMobileNumber('user'))->notRegistered()));
        $this->assertTrue($validate((new IsValidMobileNumber('user'))->notRegistered()->ignore($user)));
        $this->assertTrue($validate((new IsValidMobileNumber($user->getMorphClass(), is_morph_class: true))->registered()));
        $this->assertTrue($validate(new IsValidMobileNumber, ['number' => '']));
        $this->assertFalse($validate(new IsValidMobileNumber, ['number' => ['7771234']]));

        config()->set('mobile-verification.allowed_country_codes', ['960', '94']);

        $this->assertTrue(Validator::make(['number' => '771234567', 'country_code' => '+94'], ['number' => [new IsValidMobileNumber]])->passes());
    }
}
