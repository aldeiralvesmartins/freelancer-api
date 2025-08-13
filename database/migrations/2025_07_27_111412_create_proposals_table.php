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
        Schema::create('proposals', function (Blueprint $table) {
            $table->string('id', 24)->unique();

            $table->string('project_id', 24);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->string('freelancer_id', 24);
            $table->foreign('freelancer_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['project_id', 'freelancer_id']);

            $table->decimal('amount', 10, 2);
            $table->integer('duration'); // dias estimados
            $table->text('message')->nullable();
            $table->json('links')->nullable(); // links extras
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            $table->decimal('deposit_amount', 10, 2)->default(0)->after('amount');
            $table->enum('deposit_status', ['pending', 'paid', 'released', 'cancelled'])->default('pending')->after('deposit_amount');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
