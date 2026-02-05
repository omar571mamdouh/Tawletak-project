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
    Schema::table('restaurants', function (Blueprint $table) {
        $table->string('category', 100)->nullable()->change();
    });
}

public function down()
{
    Schema::table('restaurants', function (Blueprint $table) {
        $table->string('category', 100)->nullable(false)->change();
    });
}

};
