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
        Schema::table('avis', function (Blueprint $table) {
            $table->unsignedBigInteger("requete_file_id");
            $table->foreign("requete_file_id")->references("id")->on("requete_files");
        });
        Schema::table('session_members', function (Blueprint $table) {
            $table->boolean("is_reporter")->default(false);
        });

        Schema::table('capes', function (Blueprint $table) {
            $table->unsignedBigInteger("requete_id");
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
      

              
    }
};
