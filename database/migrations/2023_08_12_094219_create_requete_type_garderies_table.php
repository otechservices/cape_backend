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
        Schema::create('requete_type_garderies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("type_garderie_id")->nullable();
            $table->foreign("type_garderie_id")->references("id")->on("type_garderies");

            $table->unsignedBigInteger("requete_id")->nullable();
            $table->foreign("requete_id")->references("id")->on("requetes");
            
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
        Schema::dropIfExists('requete_type_garderies');
    }
};
