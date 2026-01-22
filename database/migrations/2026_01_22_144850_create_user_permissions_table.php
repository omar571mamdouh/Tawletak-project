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
        Schema::create('user_permissions', function (Blueprint $table) {
             $table->bigIncrements('id');

            // unique system name (used in code)
            $table->string('name', 150)->unique();
            // examples: create_admin, manage_roles, view_users

            // display name for dashboard UI
            $table->string('label', 200)->nullable();
            // examples: Create Admin, Manage Roles, View Users

            // grouping for dashboard (optional but useful)
            $table->string('module', 100)->nullable()->index();
            // examples: users, roles, reports, settings

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
