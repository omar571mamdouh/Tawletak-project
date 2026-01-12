<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('table_status', function (Blueprint $table) {
            // PK = table_id (one row per table)
            $table->unsignedBigInteger('table_id')->primary();

            $table->enum('status', ['available', 'reserved', 'occupied', 'out_of_service'])
                  ->default('available');

            $table->foreignId('current_reservation_id')
                ->nullable()
                ->constrained('reservations')
                ->nullOnDelete();

            $table->dateTime('occupied_since')->nullable();
            $table->dateTime('estimated_free_at')->nullable();

            // حسب الديزاين عندك: updated_at فقط
            $table->timestamp('updated_at')->nullable();

            $table->foreign('table_id')
                ->references('id')->on('tables')
                ->cascadeOnDelete();

            $table->index(['status']);
            $table->index(['estimated_free_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_status');
    }
};
