<?php

namespace Javaabu\MobileVerification\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Javaabu\Manifest\Enums\NpmScopes;
use Javaabu\Manifest\Facades\Manifest;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * What `php artisan manifest:check` asks apps to install for this package
 */
class NpmDependenciesTest extends TestCase
{
    protected const array REACT_PACKAGES = [
        '@javaabu/forms',
        '@javaabu/onu',
        'react',
        'react-dom',
        '@inertiajs/react',
    ];

    protected function tearDown(): void
    {
        File::delete(base_path('package.json'));

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $dependencies
     * @return array<string, array<string, mixed>>
     */
    protected function check(array $dependencies): array
    {
        File::put(base_path('package.json'), json_encode(['devDependencies' => (object) $dependencies]));

        Artisan::call('manifest:check', ['--json' => true]);

        return collect(json_decode(Artisan::output(), true))->keyBy('name')->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $rows
     * @return array<int, string>
     */
    protected function neededByThisPackage(array $rows): array
    {
        return array_keys(array_filter($rows, fn (array $row) => in_array('mobile-verification', $row['needed_by'] ?? [], true)));
    }

    #[Test]
    public function blade_apps_are_not_asked_for_the_react_pages_packages(): void
    {
        $needed = $this->neededByThisPackage($this->check(['vite' => '^8.0']));

        foreach (self::REACT_PACKAGES as $name) {
            $this->assertNotContains($name, $needed);
        }
    }

    #[Test]
    public function inertia_apps_are_asked_for_what_the_pages_import(): void
    {
        $rows = $this->check(['@inertiajs/react' => '^3.0']);

        foreach (self::REACT_PACKAGES as $name) {
            $this->assertContains($name, $this->neededByThisPackage($rows));
        }
    }

    #[Test]
    public function every_npm_package_is_for_the_pages(): void
    {
        $package = Manifest::packages()['mobile-verification'];

        $this->assertSame([], $package->npmDependencies(NpmScopes::ASSETS));
        $this->assertSame([], $package->npmPeerDependencies(NpmScopes::ASSETS));
        $this->assertArrayHasKey('@javaabu/forms', $package->npmDependencies(NpmScopes::PAGES));
    }
}
