<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->bigIncrements('id');

            // unique system name (used in code)
            $table->string('name', 100)->unique();   
            // examples: super_admin, admin, manager

            // display name (for dashboard UI)
            $table->string('label', 150)->nullable(); 
            // examples: Super Admin, Admin, Manager

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
