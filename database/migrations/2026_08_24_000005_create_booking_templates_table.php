<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
        });

        Schema::create('booking_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_template_id')->constrained('booking_templates')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('resource_categories')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_template_items');
        Schema::dropIfExists('booking_templates');
    }
};
