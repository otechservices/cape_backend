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
        Schema::create('requete_files', function (Blueprint $table) {
            $table->id();
            $table->string("type");
            $table->string("reference");
            $table->string("filename");
            $table->unsignedBigInteger("requete_id");
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
        Schema::dropIfExists('requete_files');
    }
};
