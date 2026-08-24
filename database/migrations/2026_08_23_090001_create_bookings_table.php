<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            // pending = awaiting approval, confirmed, cancelled, completed
            $table->string('status')->default('confirmed');
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('priority')->default(5); // 1 (highest) to 10 (lowest)
            $table->timestamps();

            // Index for fast overlap queries
            $table->index(['resource_id', 'start_at', 'end_at']);
            $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
