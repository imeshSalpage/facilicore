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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->foreignId('category_id')->constrained('resource_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->default('active'); // active, maintenance, retired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
