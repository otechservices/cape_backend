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
            $table->string("name_chief")->nullable();
            $table->string("phone_chief")->nullable();
            $table->boolean("has_cps_file")->default(false);
            $table->boolean("has_physical_deposit")->default(false);
            $table->string("name_depositor")->nullable();
            $table->string("phone_depositor")->nullable();
            $table->string("observation_depositor")->nullable();
            $table->datetime("date_depositor")->nullable();

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
