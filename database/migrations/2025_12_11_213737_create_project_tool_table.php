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
        Schema::create('project_tool', function (Blueprint $table) {
            $table->foreignId('project_id'); // FK to projects table
            $table->foreignId('tool_id');    // FK to tools table
            $table->primary(['project_id', 'tool_id']); // Composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tool');
    }
};
