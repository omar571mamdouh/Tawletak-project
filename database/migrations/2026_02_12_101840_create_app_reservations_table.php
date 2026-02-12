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
    Schema::create('app_reservations', function (Blueprint $table) {
        $table->id();
        $table->integer('restaurant_id')->nullable();
        $table->string('customer_name');
        $table->string('customer_phone', 50)->nullable();
        $table->date('date');
        $table->time('time');
        $table->integer('guests_count');
        $table->integer('table_id')->nullable();
        $table->string('code')->unique();
        $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
        $table->text('reason')->nullable();
        $table->timestamp('cancelled_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_reservations');
    }
};
