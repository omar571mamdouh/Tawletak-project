<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservation_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();

            $table->string('event_type', 50);
            $table->dateTime('event_time');

            $table->enum('actor_type', ['customer', 'staff', 'admin', 'system'])->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();

            $table->json('meta_json')->nullable();

            $table->timestamps();

            $table->index(['reservation_id', 'event_time']);
            $table->index(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_events');
    }
};
