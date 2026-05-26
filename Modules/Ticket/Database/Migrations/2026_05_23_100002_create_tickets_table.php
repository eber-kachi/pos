<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('assigned_agent_id')->nullable();
            $table->unsignedBigInteger('ticket_status_id');
            $table->string('priority', 20)->default('medium');
            $table->string('subject', 255);
            $table->text('description');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('assigned_agent_id')->references('id')->on('users');
            $table->foreign('ticket_status_id')->references('id')->on('ticket_statuses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
