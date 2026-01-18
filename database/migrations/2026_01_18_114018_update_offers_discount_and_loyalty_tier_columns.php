<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {

            // ✅ discount_value لازم يبقى nullable (عشان perk)
            $table->decimal('discount_value', 18, 2)
                ->nullable()
                ->change();

            // ✅ eligible_loyalty_tier نخليه string بدل enum
            $table->string('eligible_loyalty_tier', 50)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {

            // رجوع discount_value إجباري
            $table->decimal('discount_value', 18, 2)
                ->nullable(false)
                ->change();

            // رجوع enum القديم
            $table->enum('eligible_loyalty_tier', ['Bronze', 'Silver', 'Gold'])
                ->nullable()
                ->change();
        });
    }
};
