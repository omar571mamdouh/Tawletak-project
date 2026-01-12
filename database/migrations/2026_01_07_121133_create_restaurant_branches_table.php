<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurant_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('address', 500);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->time('opening_time');
            $table->time('closing_time');
            $table->string('timezone', 50)->default('Asia/Amman');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Helpful index for geo queries (basic)
            $table->index(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_branches');
    }
};
