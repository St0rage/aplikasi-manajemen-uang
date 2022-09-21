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
        Schema::create('piggy_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('piggy_bank_name');
            $table->decimal('piggy_bank_total', 10,2)->default(0);
            $table->boolean('type')->default(0);
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
        Schema::dropIfExists('piggy_banks');
    }
};
