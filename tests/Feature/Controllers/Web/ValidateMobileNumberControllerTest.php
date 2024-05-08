<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class ValidateMobileNumberControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test
     * @throws \JsonException
     */
    // it can validate country code
    public function it_can_validate_country_codes()
    {
        $this->post('validate', [
            'country_code' => '960',
            'number' => '7326655',
        ])
             ->assertSessionHasNoErrors();

        $this->post('validate', [
            'country_code' => '',
            'number' => '7326655',
        ])
             ->assertSessionHasNoErrors();

        $this->post('validate', [
            'country_code' => '9999999',
            'number' => '7326655',
        ])
             ->assertRedirect()
             ->assertSessionHasErrors(['country_code']);
    }

    /** @test */
    // it can validate number is required
    public function it_can_validate_number_is_required()
    {
        $this->post('validate', [
            'number' => '',
        ])->assertSessionHasErrors(['number']);
    }


    /** @test
     * @throws \JsonException
     */
    public function it_can_validate_maldivian_mobile_numbers(): void
    {
        $this->post('validate', [
            'number' => '7326655',
        ])->assertSessionHasNoErrors();

        $this->post('validate', [
            'country_code' => '960',
            'number' => '9326655',
        ])->assertSessionHasNoErrors();

        $this->post('validate', [
            'number' => '73266557',
        ])->assertSessionHasErrors(['number']);

        $this->post('/validate', [
            'number' => '3326655',
        ])->assertSessionHasErrors(['number']);
    }

    /** @test */
    public function it_can_validate_foreign_mobile_numbers(): void
    {
        $this->post('validate', [
            'country_code' => '966',
            'number' => '5407256891500',
        ])->assertSessionHasNoErrors();
    }

    /** @test */
    public function it_can_validate_if_the_mobile_number_is_already_in_use_by_another_user_of_the_same_type(): void
    {
        $other_user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'user_id' => $other_user->id,
            'user_type' => 'user',
            'number' => '7326655',
            'country_code' => '960',
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('validate', [
            'number' => '7326655',
            'country_code' => '960',
        ])->assertSessionHasErrors(['number']);
    }

    /** @test */
    public function it_can_validate_if_the_mobile_number_is_already_in_use_by_another_user_of_a_different_type(): void
    {
        $mobile_number = MobileNumber::factory()->create([
            'user_id' => 1,
            'user_type' => 'public_user',
            'number' => '7326655',
            'country_code' => '960',
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('validate', [
            'number' => '7326655',
            'country_code' => '960',
        ])->assertSessionHasNoErrors();
    }

    /** @test */
    public function it_can_validate_if_the_mobile_number_is_already_in_use_by_another_user_with_a_different_country_code(): void
    {
        $other_user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'user_id' => $other_user->id,
            'user_type' => 'user',
            'number' => '7326655',
            'country_code' => '966',
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('validate', [
            'number' => '7326655',
            'country_code' => '960',
        ])->assertSessionHasNoErrors();
    }
}
