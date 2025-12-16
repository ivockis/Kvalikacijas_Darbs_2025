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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');    // FK to users table
            $table->foreignId('project_id'); // FK to projects table
            $table->text('reason');
            $table->string('status')->default('pending'); // e.g., pending, reviewed, dismissed
            $table->timestamps();

            // Add unique constraint for user_id and project_id combination
            $table->unique(['user_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
