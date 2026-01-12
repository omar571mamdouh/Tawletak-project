<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->foreignId('branch_id')
                ->constrained('restaurant_branches')
                ->cascadeOnDelete();
            $table->foreignId('table_id')
                ->nullable()
                ->constrained('tables')
                ->nullOnDelete();
            $table->unsignedInteger('party_size');
            $table->dateTime('reservation_time');
            $table->unsignedInteger('expected_duration_minutes')->nullable()->default(90);
            $table->enum('status', [
                'pending', 'confirmed', 'rejected', 'cancelled', 'no_show', 'seated', 'completed'
            ])->default('pending');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('seated_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->enum('source', ['app', 'walk_in', 'phone'])->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'reservation_time']);
            $table->index(['customer_id', 'reservation_time']);
            $table->index(['status', 'reservation_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
