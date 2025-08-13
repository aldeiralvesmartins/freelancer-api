<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_id', 24);
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'lock', 'unlock', 'release']);
            $table->decimal('amount', 10, 2);
            $table->string('related_id')->nullable();    // pra linkar projeto, proposta etc.
            $table->string('related_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
