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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->date('invite_date');
            $table->integer('status')->default(0);
            $table->unsignedBigInteger("requete_id")->nullable();
            $table->foreign("requete_id")->references("id")->on("requetes");
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
        Schema::dropIfExists('agendas');
    }
};
