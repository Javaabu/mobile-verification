<?php

namespace Javaabu\MobileVerification\Tests\Unit\migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateMobileNumbersTableTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_migrate_up_languages_table()
    {
        $this->withoutExceptionHandling();

        $this->assertTrue(Schema::hasTable('mobile_numbers'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'user_type'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'user_id'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'attempts'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'number'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'country_code'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'verification_code'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'verification_code_created_at'));
        $this->assertTrue(Schema::hasColumn('mobile_numbers', 'verification_code_id'));
    }
}
