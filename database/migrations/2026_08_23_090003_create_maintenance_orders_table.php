<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            // Type: preventive, corrective, inspection, emergency
            $table->string('type')->default('corrective');
            // Status: scheduled, in_progress, completed, cancelled
            $table->string('status')->default('scheduled');
            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'resource_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_orders');
    }
};
