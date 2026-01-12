<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('restaurant_branches')
                ->cascadeOnDelete();

            $table->unsignedInteger('party_size');

            $table->enum('status', ['waiting', 'notified', 'seated', 'cancelled'])
                  ->default('waiting');

            $table->unsignedInteger('estimated_wait_minutes')->nullable();
            $table->dateTime('notified_at')->nullable();
            $table->dateTime('seated_at')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
