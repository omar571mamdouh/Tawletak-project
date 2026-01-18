<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();

            // مين صاحب التوكن (admin / customer)
            $table->enum('owner_type', ['admin', 'customer']);

            // ID المستخدم
            $table->unsignedBigInteger('owner_id');

            // FCM Token
            $table->string('token', 512)->unique();

            // نوع المنصة (web / android / ios)
            $table->string('platform', 20)->nullable();

            $table->timestamps();

            // Index علشان البحث السريع
            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
