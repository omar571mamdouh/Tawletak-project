<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->constrained('restaurant_branches')
                ->cascadeOnDelete();
            $table->string('table_code', 50);
            $table->unsignedInteger('capacity');
            $table->enum('location_tag', ['indoor', 'outdoor', 'vip'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['branch_id', 'table_code']);
            $table->index(['branch_id', 'capacity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
