<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection(config('mobile-verification.database_connection'));
        $table_name = config('mobile-verification.table_name', 'mobile_numbers');

        // Apps upgrading from v2 already have the table, with the same columns
        if ($schema->hasTable($table_name)) {
            return;
        }

        $schema->create($table_name, function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('user');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('number')->index();
            $table->string('country_code');
            $table->string('verification_code')->nullable();
            $table->dateTime('verification_code_created_at')->nullable();
            $table->uuid('verification_code_id')->nullable();
            $table->timestamps();

            // One number per user
            $table->unique(['user_id', 'user_type']);

            // One record per number and kind of user
            $table->unique(['country_code', 'number', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::connection(config('mobile-verification.database_connection'))
            ->dropIfExists(config('mobile-verification.table_name', 'mobile_numbers'));
    }
};
