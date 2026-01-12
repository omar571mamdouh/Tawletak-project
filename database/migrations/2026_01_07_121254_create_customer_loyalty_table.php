<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_loyalty', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();

            $table->unsignedInteger('visit_count')->default(0);

            $table->foreignId('tier_id')
                ->constrained('loyalty_tiers')
                ->restrictOnDelete();

            $table->dateTime('last_visit_at')->nullable();

            $table->timestamps();

            $table->unique(['customer_id', 'restaurant_id']);
            $table->index(['restaurant_id', 'visit_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_loyalty');
    }
};
