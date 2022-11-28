<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piggy_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piggy_bank_id')->constrained()->onDelete('cascade');
            $table->string('transaction_name');
            $table->decimal('amount', 15,2);
            $table->boolean('status');
            $table->string('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piggy_bank_transactions');
    }
};
