<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('restaurant_branches')
                ->cascadeOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            $table->enum('discount_type', ['percent', 'fixed', 'perk']);
            $table->decimal('discount_value', 18, 2);

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->unsignedInteger('min_party_size')->nullable();
            $table->enum('eligible_loyalty_tier', ['Bronze', 'Silver', 'Gold'])->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['branch_id', 'start_at', 'end_at']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
