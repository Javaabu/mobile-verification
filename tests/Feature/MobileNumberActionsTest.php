<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Events\MobileNumberUpdated;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\Fixtures\DenyAllMobileNumberPolicy;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\Permissions\Permissions;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

class MobileNumberActionsTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('permissions.guards', ['web_admin']);
    }

    protected function defineRoutes($router): void
    {
        $this->registerRoutes();

        Route::mobileNumberActions('user', ['prefix' => 'admin/users', 'middleware' => ['web', 'auth.type:admin']]);
        Route::mobileNumberActions('admin', ['prefix' => 'admin/admins', 'middleware' => ['web', 'auth.type:admin']]);
    }

    protected function admin(array $permissions = []): Admin
    {
        $this->artisan('permissions:sync');

        $admin = Admin::factory()->withMobileNumber('7770000')->create();

        if ($permissions) {
            $admin->givePermissionTo($permissions);
        }

        return $admin;
    }

    #[Test]
    public function it_registers_permissions_for_each_user_type(): void
    {
        $this->assertTrue(Permissions::has('update_user_mobile_numbers'));
        $this->assertTrue(Permissions::has('unlock_admin_mobile_numbers'));
        $this->assertTrue(Permissions::has('remove_user_mobile_numbers'));
        $this->assertSame('Change the mobile numbers of users', Permissions::description('update_user_mobile_numbers'));
        $this->assertSame('users', Permissions::groupOf('update_user_mobile_numbers')->name);
    }

    #[Test]
    public function admins_need_the_permission(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($this->admin(), 'web_admin')
            ->put("/admin/users/{$user->id}/mobile-number", ['number' => '9991234'])
            ->assertForbidden();

        $this->post("/admin/users/{$user->id}/mobile-number/unlock")->assertForbidden();
        $this->delete("/admin/users/{$user->id}/mobile-number")->assertForbidden();

        $this->assertSame('+9607771234', $user->fresh()->formatted_mobile_number);
    }

    #[Test]
    public function guests_and_users_cannot_use_the_actions(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->put("/admin/users/{$user->id}/mobile-number", ['number' => '9991234'])->assertRedirect('/admin/login');

        $this->actingAs($user, 'web')
            ->put("/admin/users/{$user->id}/mobile-number", ['number' => '9991234'])
            ->assertRedirect('/admin/login');
    }

    #[Test]
    public function admins_change_a_users_number_without_a_code(): void
    {
        Event::fake([MobileNumberUpdated::class]);

        $user = User::factory()->withMobileNumber('7771234')->create();
        $admin = $this->admin(['update_user_mobile_numbers']);

        $this->actingAs($admin, 'web_admin')
            ->from('/admin/users')
            ->put("/admin/users/{$user->id}/mobile-number", ['number' => '9991234'])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('status', 'The mobile number has been changed to +9609991234.');

        $this->assertSame('+9609991234', $user->fresh()->formatted_mobile_number);

        Event::assertDispatched(MobileNumberUpdated::class, fn (MobileNumberUpdated $event) => $event->causer->is($admin));
    }

    #[Test]
    public function the_number_must_be_free(): void
    {
        User::factory()->withMobileNumber('9991234')->create();
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($this->admin(['update_user_mobile_numbers']), 'web_admin')
            ->putJson("/admin/users/{$user->id}/mobile-number", ['number' => '9991234'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['number' => 'This mobile number is already registered.']);

        // Their own number is fine
        $this->putJson("/admin/users/{$user->id}/mobile-number", ['number' => '7771234'])->assertOk();
    }

    #[Test]
    public function admins_unlock_and_remove_numbers(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();
        $user->phone->forceFill(['attempts' => 5, 'verification_code_created_at' => now()])->save();

        $this->assertTrue($user->phone->fresh()->isLocked());

        $this->actingAs($this->admin(['unlock_user_mobile_numbers', 'remove_user_mobile_numbers']), 'web_admin')
            ->postJson("/admin/users/{$user->id}/mobile-number/unlock")
            ->assertOk()
            ->assertJsonPath('message', 'The mobile number has been unlocked.');

        $this->assertFalse($user->phone->fresh()->isLocked());

        $this->deleteJson("/admin/users/{$user->id}/mobile-number")->assertOk();

        $this->assertFalse($user->fresh()->hasVerifiedMobile());
        $this->assertSame(2, MobileNumber::query()->count());
    }

    #[Test]
    public function permissions_are_per_user_type(): void
    {
        $other = Admin::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($this->admin(['update_user_mobile_numbers']), 'web_admin')
            ->putJson("/admin/admins/{$other->id}/mobile-number", ['number' => '9991234'])
            ->assertForbidden();
    }

    #[Test]
    public function unknown_accounts_are_not_found(): void
    {
        $this->actingAs($this->admin(['update_user_mobile_numbers']), 'web_admin')
            ->putJson('/admin/users/999/mobile-number', ['number' => '9991234'])
            ->assertNotFound();
    }

    #[Test]
    #[DefineEnvironment('useDenyAllPolicy')]
    public function the_policy_can_be_replaced_in_config(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($this->admin(['update_user_mobile_numbers']), 'web_admin')
            ->putJson("/admin/users/{$user->id}/mobile-number", ['number' => '9991234'])
            ->assertForbidden();
    }

    #[Test]
    public function abilities_can_be_redefined_on_the_gate(): void
    {
        Gate::define('mobile-verification.remove', fn () => true);

        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($this->admin(), 'web_admin')
            ->deleteJson("/admin/users/{$user->id}/mobile-number")
            ->assertOk();
    }

    #[Test]
    #[DefineEnvironment('turnOffPermissions')]
    public function permissions_can_be_turned_off(): void
    {
        $this->assertFalse(Permissions::has('update_user_mobile_numbers'));
    }

    protected function useDenyAllPolicy($app): void
    {
        $app['config']->set('mobile-verification.policy', DenyAllMobileNumberPolicy::class);
    }

    protected function turnOffPermissions($app): void
    {
        $app['config']->set('mobile-verification.register_permissions', false);
    }
}
