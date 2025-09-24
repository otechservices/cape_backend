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
           Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->unsignedBigInteger("municipality_id")->nullable();
            $table->foreign("municipality_id")->references("id")->on("municipalities");
            $table->unsignedBigInteger("cps_id")->nullable();
            $table->foreign("cps_id")->references("id")->on("cps");
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
        Schema::dropIfExists('districts');
    }
};
