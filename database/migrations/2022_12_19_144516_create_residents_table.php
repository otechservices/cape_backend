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
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string("firstname");
            $table->string("lastname");
            $table->string("birthdate");
            $table->string("birthplace");
            $table->string("address");
            $table->string("sex");
            $table->string("size");
            $table->string("weight");
            $table->string("identity_father")->nullable();
            $table->string("phone_father")->nullable();
            $table->string("identity_mother")->nullable();
            $table->string("phone_mother")->nullable();
            $table->string("identity_titor")->nullable();
            $table->string("phone_titor")->nullable();
            $table->unsignedBigInteger("cape_id");
            $table->foreign("cape_id")->references("id")->on("capes");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('residents');
    }
};
