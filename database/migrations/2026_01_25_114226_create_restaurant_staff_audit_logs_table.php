<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurant_staff_audit_logs', function (Blueprint $table) {
            $table->id();

            // Tenant scope
            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();

            // Optional branch scope (لو staff مربوط بفرع أو الحدث مرتبط بفرع)
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('restaurant_branches')
                ->nullOnDelete();

            // Actor (مين عمل الحدث)
            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('restaurant_staff')
                ->nullOnDelete();

            // Event / action key
            $table->string('action', 120)->index(); // e.g. tables.create, offers.update, reservations.confirm

            // Target entity (اختياري)
            $table->string('entity_type', 120)->nullable(); // Table, Offer, Reservation...
            $table->unsignedBigInteger('entity_id')->nullable();

            // Request context
            $table->string('method', 10)->nullable();
            $table->string('path', 500)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('ip', 45)->nullable(); // IPv4/IPv6
            $table->string('user_agent', 500)->nullable();

            // Payload / diff
            $table->json('meta')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();

            $table->timestamps();

            // Indexes للأداء (Owner هيعمل فلترة كتير)
            $table->index(['restaurant_id', 'created_at'], 'rsal_rest_created_idx');
            $table->index(['restaurant_id', 'staff_id', 'created_at'], 'rsal_rest_staff_created_idx');
            $table->index(['restaurant_id', 'branch_id', 'created_at'], 'rsal_rest_branch_created_idx');
            $table->index(['restaurant_id', 'action', 'created_at'], 'rsal_rest_action_created_idx');
            $table->index(['entity_type', 'entity_id'], 'rsal_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_staff_audit_logs');
    }
};
