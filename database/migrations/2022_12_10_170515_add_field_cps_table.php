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
        Schema::table('cps', function (Blueprint $table) {
            $table->string("acronym");
            $table->string("name_chief");
            $table->string("phone");
            $table->string("email");
            $table->unsignedBigInteger("municipality_id")->nullable();
            $table->foreign("municipality_id")->references("id")->on("municipalities");

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
