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
        Schema::table('requetes', function (Blueprint $table) {
           // $table->boolean("has_agreemant")->nullable();
          //  $table->dropColumn("center_type");
         //   $table->text("observation")->nullable();

        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->string("filename")->nullable();

        });
        Schema::table('referals', function (Blueprint $table) {
          
            $table->unsignedBigInteger("requete_id")->nullable();
            $table->foreign("requete_id")->references("id")->on("requetes");
            $table->unsignedBigInteger("cape_id")->nullable();
            $table->foreign("cape_id")->references("id")->on("capes");
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
