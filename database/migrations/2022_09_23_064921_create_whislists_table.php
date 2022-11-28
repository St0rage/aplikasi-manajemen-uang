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
        Schema::create('whislists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('whislist_name');
            $table->decimal('whislist_total', 15,2)->default(0);
            $table->decimal('whislist_target', 15,2);
            $table->decimal('progress', 5,2)->default(0);
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
        Schema::dropIfExists('whislists');
    }
};
