<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('restaurant_branches', function (Blueprint $table) {
        $table->time('opening_time')->nullable()->change();
        $table->time('closing_time')->nullable()->change();
    });
}

public function down()
{
    Schema::table('restaurant_branches', function (Blueprint $table) {
        $table->time('opening_time')->nullable(false)->change();
        $table->time('closing_time')->nullable(false)->change();
    });
}

};
