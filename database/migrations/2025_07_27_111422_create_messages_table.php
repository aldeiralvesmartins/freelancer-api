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
        Schema::create('messages', function (Blueprint $table) {
            $table->string('id', 24)->unique();

            $table->string('sender_id', 24);
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('receiver_id', 24);
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('project_id', 24);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->text('content');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
