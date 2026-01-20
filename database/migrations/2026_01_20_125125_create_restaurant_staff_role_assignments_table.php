<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurant_staff_role_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('staff_id')
                ->constrained('restaurant_staff')
                ->cascadeOnDelete();

            $table->foreignId('restaurant_role_id')
                ->constrained('restaurant_roles')
                ->cascadeOnDelete();

            $table->timestamps();

            // staff role واحد فقط (زي ما اتفقنا)
            $table->unique(['staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_staff_role_assignments');
    }
};
