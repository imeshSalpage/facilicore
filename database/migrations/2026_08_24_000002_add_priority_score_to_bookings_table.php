<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('urgency')->default(false)->after('notes');
            $table->decimal('priority_score', 8, 2)->default(0.00)->after('urgency');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['urgency', 'priority_score']);
        });
    }
};
