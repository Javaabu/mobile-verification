<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Tests\TestCase;

class TokenValidationControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function it_can_validate_the_token(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_can_validate_if_the_token_is_expired(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    public function it_can_validate_if_the_token_is_invalid(): void
    {
        $this->assertFalse(true);
    }

    /** @test */
    // TODO: check etukuri and add all the token validation tests

}
