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
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            // Foreign key to the tickets table
            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->onDelete('cascade'); // If a ticket is deleted, its replies are also deleted

            // Foreign key to the users table (who made the reply)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // If a user is deleted, their replies are also deleted (adjust if needed)

            $table->text('content'); // The actual reply content
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
