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
        Schema::table('offer_redemptions', function (Blueprint $table) {
            $table->unique(['offer_id', 'customer_id'], 'offer_customer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_redemptions', function (Blueprint $table) {
             $table->dropUnique('offer_customer_unique');
        });
    }
};
