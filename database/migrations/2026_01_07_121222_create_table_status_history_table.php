<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('table_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('table_id')
                ->constrained('tables')
                ->cascadeOnDelete();

            $table->foreignId('changed_by_staff_id')
                ->nullable()
                ->constrained('restaurant_staff')
                ->nullOnDelete();

            $table->enum('old_status', ['available', 'reserved', 'occupied', 'out_of_service']);
            $table->enum('new_status', ['available', 'reserved', 'occupied', 'out_of_service']);

            $table->dateTime('timestamp');
            $table->string('note', 500)->nullable();

            // اختياري: لو عايزة timestamps هنا كمان
            $table->timestamps();

            $table->index(['table_id', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_status_history');
    }
};
