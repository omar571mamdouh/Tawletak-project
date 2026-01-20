<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurant_role_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_role_id')
                ->constrained('restaurant_roles')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();

            $table->timestamps();

            // اسم قصير عشان MySQL limit
            $table->unique(['restaurant_role_id', 'permission_id'], 'rrp_role_perm_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_role_permissions');
    }
};
