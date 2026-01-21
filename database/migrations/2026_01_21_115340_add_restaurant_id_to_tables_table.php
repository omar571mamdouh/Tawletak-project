<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            // 1️⃣ أضف العمود nullable مؤقتًا
            $table->unsignedBigInteger('restaurant_id')->nullable()->after('id');
        });

        // 2️⃣ عبّي العمود القديم بالقيمة المناسبة حسب branch → restaurant
        DB::table('tables')
            ->join('restaurant_branches', 'tables.branch_id', '=', 'restaurant_branches.id')
            ->update([
                'tables.restaurant_id' => DB::raw('restaurant_branches.restaurant_id')
            ]);

        // 3️⃣ بعد ما العمود اتملأ، نضيف الـ foreign key + not nullable
        Schema::table('tables', function (Blueprint $table) {
            $table->foreign('restaurant_id')
                  ->references('id')
                  ->on('restaurants')
                  ->cascadeOnDelete();

            // تأكد uniqueness على مستوى المطعم + table_code
            $table->unique(['restaurant_id', 'table_code']);

            // اجعل العمود NOT NULL بعد ما اتملأ
            $table->unsignedBigInteger('restaurant_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            // إسقاط الـ foreign key
            $table->dropForeign(['restaurant_id']);

            // إسقاط الـ unique index
            $table->dropUnique(['restaurant_id', 'table_code']);

            // إسقاط العمود
            $table->dropColumn('restaurant_id');
        });
    }
};
