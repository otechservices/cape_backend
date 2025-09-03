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
        Schema::table('control_file_elements', function (Blueprint $table) {
            
            $table->unsignedBigInteger("service_id");
            $table->foreign("service_id")->references("id")->on("services");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('control_file_elements', function (Blueprint $table) {
            //
        });
    }
};
