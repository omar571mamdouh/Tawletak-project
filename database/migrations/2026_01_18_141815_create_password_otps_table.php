<?php

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
        Schema::create('password_otps', function ($table) {
    $table->id();
    $table->string('identifier'); // email or phone
    $table->string('otp_hash');
    $table->string('reset_token')->nullable();
    $table->timestamp('expires_at');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();

    $table->index(['identifier']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_otps');
    }
};
