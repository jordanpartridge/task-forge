<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique(); // TF-1, PROJ-1, etc.
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Core Status Fields
            $table->string('status')->default('active'); // active, paused, completed
            $table->string('priority')->default('medium'); // low, medium, high

            // Basic Dates
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            // Ownership
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('lead_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Essential indexes
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
