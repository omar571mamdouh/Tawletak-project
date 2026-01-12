<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->enum('recipient_type', ['customer', 'admin']);
            $table->unsignedBigInteger('recipient_id');

            $table->string('type', 100);
            $table->string('title', 200);
            $table->text('message');

            $table->json('data_json')->nullable();

            $table->boolean('is_read')->default(false);
            $table->dateTime('sent_at');

            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['type', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
