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
        Schema::create('reponses', function (Blueprint $table) {
            
            $table->id();
            $table->integer('hasPermission');
            $table->text('reason')->nullable();
            $table->text('observation')->nullable();

            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on("users");

            $table->unsignedBigInteger("requete_id")->nullable();
            $table->foreign("requete_id")->references("id")->on("requetes");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reponses', function (Blueprint $table) {
            //
        });
    }
};
