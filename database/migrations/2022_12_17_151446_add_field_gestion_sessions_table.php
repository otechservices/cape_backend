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
     /*   Schema::table('capes', function (Blueprint $table) {
            $table->longText("decision")->nullable();
        });*/

        Schema::create('referals', function (Blueprint $table) {
            $table->text("libelle")->nullable();
            $table->boolean("is_reached")->default(false);
        });


        Schema::create('referal_controls', function (Blueprint $table) {
            $table->text("libelle")->nullable();
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
