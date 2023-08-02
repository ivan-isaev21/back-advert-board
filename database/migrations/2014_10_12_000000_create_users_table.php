<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('login')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('status', 16);
            $table->string('verify_token')->nullable()->unique();
            $table->string('role', 16)->default(User::ROLE_USER);
            $table->string('phone')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('phone_verify_token')->nullable();
            $table->timestamp('phone_verify_token_expire')->nullable();
            $table->boolean('phone_auth')->default(false);
            $table->string('password_reset_token')->nullable();
            $table->string('password_reset_token_expire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
