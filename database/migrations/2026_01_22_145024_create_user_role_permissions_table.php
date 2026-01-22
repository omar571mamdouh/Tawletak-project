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
        Schema::create('user_role_permissions', function (Blueprint $table) {
             $table->unsignedBigInteger('role_id');
    $table->unsignedBigInteger('permission_id');

    $table->primary(['role_id', 'permission_id']);

    $table->foreign('role_id')
        ->references('id')->on('user_roles')
        ->onDelete('cascade');

    $table->foreign('permission_id')
        ->references('id')->on('user_permissions')
        ->onDelete('cascade');

    $table->index('role_id');
    $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role_permissions');
    }
};
