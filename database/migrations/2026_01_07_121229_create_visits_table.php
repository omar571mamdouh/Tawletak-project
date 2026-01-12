<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('restaurant_branches')
                ->cascadeOnDelete();

            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained('reservations')
                ->nullOnDelete();

            $table->foreignId('table_id')
                ->nullable()
                ->constrained('tables')
                ->nullOnDelete();

            $table->dateTime('seated_at');
            $table->dateTime('left_at')->nullable();

            $table->decimal('bill_amount', 18, 2)->nullable();

            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');

            $table->timestamps();

            $table->index(['branch_id', 'seated_at']);
            $table->index(['customer_id', 'seated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
