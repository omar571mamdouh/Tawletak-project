<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurant_branches', function (Blueprint $table) {

            $table->string('address', 500)->nullable()->change();

            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();

            $table->time('opening_time')->nullable()->change();
            $table->time('closing_time')->nullable()->change();

        });
    }

    public function down(): void
    {
        Schema::table('restaurant_branches', function (Blueprint $table) {

            $table->string('address', 500)->nullable(false)->change();

            $table->decimal('lat', 10, 7)->nullable(false)->change();
            $table->decimal('lng', 10, 7)->nullable(false)->change();

            $table->time('opening_time')->nullable(false)->change();
            $table->time('closing_time')->nullable(false)->change();

        });
    }
};
