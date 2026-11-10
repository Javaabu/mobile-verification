<?php

namespace Javaabu\MobileVerification\Views;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Inertia\Inertia;
use Javaabu\Auth\Support\PageData;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Support\Strings;
use RuntimeException;

/**
 * Renders a page with Blade (`mobile-verification::login`) or Inertia (`mobile-verification::Login`):
 * `mobile-verification.views`, or the user type's stack when that's null
 */
class ViewRenderer
{
    public const string BLADE = 'blade';

    public const string INERTIA = 'inertia';

    public function __construct(
        protected PageData $page_data,
    ) {}

    public function viewStack(UserType $type): string
    {
        return MobileVerification::config('views') ?: $type->viewStack();
    }

    /**
     * @param  string  $page  e.g. `login` or `settings.mobile-number`
     * @param  array<string, mixed>  $data
     * @param  string  $layout  `auth` for guest pages, `account` for signed-in pages
     */
    public function render(UserType $type, string $page, array $data = [], string $layout = 'auth'): View|Responsable
    {
        $data = [...$this->sharedData($type), ...$data];

        if ($this->viewStack($type) === self::INERTIA) {
            return $this->renderInertia($page, $data);
        }

        return view("mobile-verification::{$page}", [
            ...$data,
            'user_type' => $type,
            'layout' => $type->layoutName($layout),
            'layout_section' => config('javaabu-auth.layout_section', 'content'),
        ]);
    }

    /**
     * The Inertia page name for a page: `settings.mobile-number` → `mobile-verification::Settings/MobileNumber`
     */
    public function inertiaPage(string $page): string
    {
        $segments = array_map(fn (string $segment) => str($segment)->studly()->toString(), explode('.', $page));

        return 'mobile-verification::' . implode('/', $segments);
    }

    /** @return array<string, mixed> */
    public function sharedData(UserType $type): array
    {
        return [
            ...$this->page_data->shared($type),
            'mobile' => [
                'country_codes' => MobileVerification::countryCodeOptions(),
                'default_country_code' => MobileVerification::defaultCountryCode(),
                'number_prefix' => MobileVerification::numberPrefix(),
                'code_length' => (int) MobileVerification::config('code_length', 6),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderInertia(string $page, array $data): Responsable
    {
        if (! class_exists(Inertia::class)) {
            throw new RuntimeException('Install inertiajs/inertia-laravel to use the inertia views, or set mobile-verification.views to blade.');
        }

        return Inertia::render($this->inertiaPage($page), [
            ...$data,
            // javaabu/auth's strings too, for its AccountLayout
            'strings' => [...$this->page_data->strings(), ...Strings::translated()],
        ]);
    }
}
