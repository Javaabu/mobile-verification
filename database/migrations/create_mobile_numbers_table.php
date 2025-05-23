<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('mobile-verification.database_connection'))
            ->create(config('mobile-verification.table_name'), function (Blueprint $table) {
                $table->id();
                $table->nullableMorphs('user');
                $table->unsignedInteger('attempts')->default(0);
                $table->string('number')->index();
                $table->string('country_code');
                $table->string('verification_code')->nullable();
                $table->dateTime('verification_code_created_at')->nullable();
                $table->uuid('verification_code_id')->nullable();
                $table->timestamps();

                // 1 number per user
                $table->unique(['user_id', 'user_type']);

                // 1 record for each number
                $table->unique(['country_code', 'number', 'user_type']);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('mobile-verification.database_connection'))->dropIfExists(config('mobile-verification.table_name'));
    }
};
