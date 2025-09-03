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
            $table->string('registered_number')->nullable();
            $table->date('registered_date')->nullable();
            $table->string('head_office')->nullable();
            $table->string('registered_proof')->nullable();
            $table->unsignedBigInteger("nature_promotor_id")->nullable();
            $table->foreign("nature_promotor_id")->references("id")->on("nature_promotors");
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
