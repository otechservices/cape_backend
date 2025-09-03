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
        Schema::create('transmission_controls', function (Blueprint $table) {
            $table->id();
            $table->boolean('isLast')->default(true);

            $table->text('instruction')->nullable();
            $table->datetime('delay')->nullable();


            $table->unsignedBigInteger('user_up');
            $table->foreign('user_up') ->references('id')->on('users');

            $table->unsignedBigInteger('user_down');
            $table->foreign('user_down')->references('id')->on('users');

            $table->unsignedBigInteger('control_id');
            $table->foreign('control_id')->references('id')->on('controls');

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
        Schema::dropIfExists('_cotransmission_ntrols');
    }
};
