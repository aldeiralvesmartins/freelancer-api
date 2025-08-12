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
        Schema::create('ratings', function (Blueprint $table) {
            $table->string('id', 24)->unique();

            $table->string('from_user_id', 24);
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('to_user_id', 24);
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('project_id', 24);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->tinyInteger('rating'); // de 1 a 5
            $table->text('comment')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
