<?php

namespace Javaabu\MobileVerification\Tests\Http;

use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BladePagesTest extends TestCase
{
    #[Test]
    public function the_pages_render_in_dhivehi(): void
    {
        $this->app->setLocale('dv');

        $this->get('/login/mobile')
            ->assertOk()
            ->assertSee('<html lang="dv" dir="rtl">', false)
            ->assertSee('މޯބައިލް ނަންބަރާއެކު ލޮގިން ކުރޭ')
            ->assertSee('ކޯޑު ފޮނުވާ');

        $this->get('/register/mobile')->assertOk()->assertSee('މޯބައިލް ނަންބަރާއެކު ސައިން އަޕް ކުރޭ');
    }

    #[Test]
    public function numbers_and_codes_stay_left_to_right(): void
    {
        $this->app->setLocale('dv');

        User::factory()->withMobileNumber('7771234')->create();

        $this->get('/login/mobile')->assertSee('name="number"', false)->assertSee('dir="ltr"', false);

        $this->post('/login/mobile', ['number' => '7771234']);

        $this->get('/login/mobile/verify')
            ->assertOk()
            ->assertSee('<bdi dir="ltr">+9607771234</bdi>', false)
            ->assertSee('ލޮގިން ވުމަށް ކޯޑު ޖައްސަވާ');
    }

    #[Test]
    public function the_messages_are_in_dhivehi(): void
    {
        $this->app->setLocale('dv');

        $this->from('/login/mobile')
            ->post('/login/mobile', ['number' => '7770000'])
            ->assertSessionHasErrors(['number' => 'މި މޯބައިލް ނަންބަރާ ގުޅޭ އެކައުންޓެއް ނެތެވެ.']);

        $this->post('/login/mobile', ['number' => '1234'])
            ->assertSessionHasErrors(['number' => 'މޯބައިލް ނަންބަރު އަކީ ރަނގަޅު މޯބައިލް ނަންބަރެއް ނޫނެވެ.']);
    }

    #[Test]
    public function the_sms_is_sent_in_the_language_of_the_page(): void
    {
        $this->app->setLocale('dv');

        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);

        $code = $this->sentCode();
        $notification = new VerificationCodeNotification($code);

        $this->assertSame('dv', $notification->locale);
        $this->assertStringContainsString("Javaabu ގެ ވެރިފިކޭޝަން ކޯޑަކީ {$code}.", $notification->toSms(null));

        $this->app->setLocale('en');

        $this->assertSame("Your Javaabu verification code is {$code}. It expires in 10 minutes. Do not share it with anyone.", (new VerificationCodeNotification($code))->toSms(null));
    }

    #[Test]
    public function the_details_form_types_thaana_on_dhivehi_pages(): void
    {
        $this->app->setLocale('dv');

        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        $this->get('/register/mobile/details')
            ->assertOk()
            ->assertSee('ތިޔަ ފަރާތުގެ މަޢުލޫމާތު')
            ->assertSee('thaana', false);

        $this->post('/register/mobile/details', ['name' => 'އަޙްމަދު ޢަލީ'])->assertRedirect('/dashboard');

        $this->assertSame('އަޙްމަދު ޢަލީ', User::query()->sole()->name);
    }

    #[Test]
    public function the_views_use_logical_css_only(): void
    {
        $files = [
            ...glob(__DIR__ . '/../../resources/views/*.blade.php'),
            ...glob(__DIR__ . '/../../resources/views/*/*.blade.php'),
            ...glob(__DIR__ . '/../../resources/js/*/*.tsx'),
            ...glob(__DIR__ . '/../../resources/js/*/*/*.tsx'),
        ];

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:ml|mr|pl|pr|left|right|text-left|text-right|border-l|border-r|rounded-l|rounded-r)-/',
                file_get_contents($file),
                basename($file) . ' uses a physical direction utility; use ms-/me-/ps-/pe-/start-/end-',
            );
        }
    }
}
