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
        Schema::create('controls', function (Blueprint $table) {
            $table->id();
            $table->longText("observation");
            $table->date("date_control");
            $table->unsignedBigInteger("cape_id");
            $table->foreign("cape_id")->references("id")->on("capes");
            $table->unsignedBigInteger("type_control_id");
            $table->foreign("type_control_id")->references("id")->on("type_controls");
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on("users");
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
        Schema::dropIfExists('controls');
    }
};
