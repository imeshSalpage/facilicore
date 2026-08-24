<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('composite_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('confirmed');
            $table->text('notes')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
        });

        // Add composite_booking_id foreign key to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('composite_booking_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('composite_bookings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['composite_booking_id']);
            $table->dropColumn('composite_booking_id');
        });

        Schema::dropIfExists('composite_bookings');
    }
};
