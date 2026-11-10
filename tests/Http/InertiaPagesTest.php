<?php

namespace Javaabu\MobileVerification\Tests\Http;

use Inertia\ServiceProvider as InertiaServiceProvider;
use Inertia\Testing\AssertableInertia;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('optional')]
class InertiaPagesTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), InertiaServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('javaabu-auth.views', 'inertia');
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
        $app['config']->set('inertia.root_view', 'app');
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function guestPages(): array
    {
        return [
            'login' => ['/login/mobile', 'mobile-verification::Login'],
            'register' => ['/register/mobile', 'mobile-verification::Register'],
            'admin login' => ['/admin/login/mobile', 'mobile-verification::Login'],
        ];
    }

    #[Test]
    #[DataProvider('guestPages')]
    public function it_renders_the_guest_pages_as_inertia_pages(string $url, string $component): void
    {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component($component, false)
                ->has('auth_type.name')
                ->where('mobile.country_codes', ['960' => '+960'])
                ->where('mobile.code_length', 6)
                ->has('strings.send_code')
                ->has('strings.log_out'));
    }

    #[Test]
    public function every_page_file_exists(): void
    {
        foreach (['Login', 'Register', 'Verify', 'RegisterDetails', 'Settings/MobileNumber'] as $page) {
            $this->assertFileExists(__DIR__ . "/../../resources/js/Pages/{$page}.tsx");
        }
    }

    #[Test]
    public function the_login_page_props_match_the_type(): void
    {
        $this->get('/admin/login/mobile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth_type.name', 'admin')
                ->where('urls', fn ($urls) => $urls['login.mobile.store'] === url('/admin/login/mobile') && $urls['login'] === url('/admin/login'))
                ->where('can_register', false)
                ->etc());

        $this->get('/login/mobile')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('can_register', true)->etc());
    }

    #[Test]
    public function the_code_page_gets_the_pending_number(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);

        $this->get('/login/mobile/verify')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('mobile-verification::Verify', false)
                ->where('purpose', 'login')
                ->where('mobile_number.formatted_number', '+9607771234')
                ->where('mobile_number.expires_in', 600)
                ->where('mobile_number.resend_in', 30)
                ->where('verify_url', url('/login/mobile/verify'))
                ->where('resend_url', url('/login/mobile/resend'))
                ->etc());
    }

    #[Test]
    public function the_details_page_gets_the_stencil_schema(): void
    {
        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        $this->get('/register/mobile/details')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('mobile-verification::RegisterDetails', false)
                ->where('form.stencil', 1)
                ->where('form.action', url('/register/mobile/details'))
                ->where('form.components.0.name', 'name')
                ->where('form.components.1.name', 'email')
                ->where('mobile_number.formatted_number', '+9607771234')
                ->etc());
    }

    #[Test]
    public function the_settings_page_gets_the_current_number(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($user, 'web')
            ->get('/settings/mobile-number')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('mobile-verification::Settings/MobileNumber', false)
                ->where('current_number.formatted_number', '+9607771234')
                ->where('account_links', fn ($links) => collect($links)->contains(fn ($link) => $link['key'] === 'mobile-number'
                    && $link['url'] === url('/settings/mobile-number')
                    && $link['active'] === true))
                ->where('urls', fn ($urls) => $urls['mobile-number.store'] === url('/settings/mobile-number'))
                ->etc());
    }

    #[Test]
    public function the_stack_can_be_chosen_for_the_package_alone(): void
    {
        config()->set('mobile-verification.views', 'blade');

        $this->get('/login/mobile')->assertOk()->assertSee('data-mobile-page="login"', false);
    }

    #[Test]
    public function the_strings_are_translated(): void
    {
        $this->app->setLocale('dv');

        $this->get('/login/mobile')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('strings.send_code', 'ކޯޑު ފޮނުވާ')
                ->where('strings.log_in_with_mobile', 'މޯބައިލް ނަންބަރާއެކު ލޮގިން ކުރޭ')
                ->etc());
    }
}
