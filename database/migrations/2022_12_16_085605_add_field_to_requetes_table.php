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
            $table->string("capacity")->nullable();
            $table->string("purpose")->nullable();

            $table->string("social_investigator_name")->nullable();
            $table->datetime("social_investigator_date")->nullable();
            $table->text("social_investigator_observation")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requetes', function (Blueprint $table) {
            //
        });
    }
};
