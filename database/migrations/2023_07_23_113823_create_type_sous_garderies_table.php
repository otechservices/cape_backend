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
        // Schema::create('type_sous_garderies', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->unsignedBigInteger("type_garderie_id");
        //     $table->foreign("type_garderie_id")->references("id")->on("type_garderies");
        //     $table->timestamps();
        // });

        Schema::table('requetes', function (Blueprint $table) {
        //    $table->unsignedBigInteger("service_id")->default(1);
           // $table->foreign("service_id")->references("id")->on("services");
        });

        Schema::table('requetes', function (Blueprint $table) {
            // $table->unsignedBigInteger("type_sous_garderie_id")->nullable();
            // $table->foreign("type_sous_garderie_id")->references("id")->on("type_sous_garderies");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_sous_garderies');
    }
};
