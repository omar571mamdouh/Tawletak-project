<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_section_id')->constrained('menu_sections')->cascadeOnDelete();

    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2)->default(0);
    $table->string('image')->nullable();

    $table->boolean('is_available')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->unsignedInteger('sort_order')->default(0);

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
