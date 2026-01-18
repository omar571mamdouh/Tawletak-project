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
        Schema::table('table_status_history', function (Blueprint $table) {
            // فكّ الـ FK القديم
            $table->dropForeign(['changed_by_staff_id']);

            // نغيّر اسم العمود ليبقى واضح إنه User
            $table->renameColumn('changed_by_staff_id', 'changed_by_user_id');
        });

        Schema::table('table_status_history', function (Blueprint $table) {
            // نربط العمود الجديد بجدول users
            $table->foreign('changed_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_status_history', function (Blueprint $table) {
            $table->dropForeign(['changed_by_user_id']);
            $table->renameColumn('changed_by_user_id', 'changed_by_staff_id');

            $table->foreign('changed_by_staff_id')
                ->references('id')
                ->on('restaurant_staff')
                ->nullOnDelete();
        });
    }
};
