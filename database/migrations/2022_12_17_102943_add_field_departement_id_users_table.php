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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger("department_id")->nullable();
            $table->foreign("department_id")->references("id")->on("departments");

        });

        Schema::table('requetes', function (Blueprint $table) {
            $table->string("firstname_pomoter")->nullable();
            $table->string("firstname_chief")->nullable();

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
