<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'in_progress', 'closed', 'reopened'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Foreign key for the user who created the ticket
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->onDelete('cascade'); // If user is deleted, their tickets are also deleted

            // Foreign key for the user assigned to the ticket (nullable)
            $table->foreignId('assigned_to_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null'); // If assigned user is deleted, set assigned_to_user_id to null

            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
