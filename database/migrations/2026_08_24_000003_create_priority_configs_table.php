<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priority_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('factor'); // role_admin, role_supervisor, role_end_user, urgency_flag, resource_demand
            $table->decimal('weight', 8, 2)->default(1.00);
            $table->timestamps();

            $table->unique(['tenant_id', 'factor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_configs');
    }
};
