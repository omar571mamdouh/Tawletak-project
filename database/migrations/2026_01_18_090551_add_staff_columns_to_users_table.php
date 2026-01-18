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
        Schema::table('users', function (Blueprint $table) {
             // رقم الموبايل (اختياري)
            $table->string('phone', 50)
                ->nullable()
                ->after('name');

            // ربط المستخدم بالمطعم
            $table->foreignId('restaurant_id')
                ->nullable()
                ->after('role')
                ->constrained('restaurants')
                ->nullOnDelete();

            // ربط المستخدم بالفرع
            $table->foreignId('branch_id')
                ->nullable()
                ->after('restaurant_id')
                ->constrained('restaurant_branches')
                ->nullOnDelete();

            $table->index(['restaurant_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            $table->dropForeign(['restaurant_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['restaurant_id', 'branch_id']);

            $table->dropColumn([
                'phone',
                'restaurant_id',
                'branch_id',
            ]);
        });
    }
};
