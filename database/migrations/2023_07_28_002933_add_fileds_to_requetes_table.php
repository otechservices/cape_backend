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
            // $table->string('registered_phone')->nullable();
            // $table->dropForeign(['type_sous_garderie_id']);
            // $table->dropColumn(['type_sous_garderie_id']);
            // $table->unsignedBigInteger("type_garderie_id")->nullable();
            // $table->foreign("type_garderie_id")->references("id")->on("type_garderies");
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
