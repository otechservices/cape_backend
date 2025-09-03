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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string("firstname");
            $table->string("lastname");
            $table->string("birthdate");
            $table->string("birthplace");
            $table->string("address");
            $table->string("phone");
            $table->string("email")->unique();
            $table->string("job");
            $table->unsignedBigInteger("cape_id")->nullable();
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
        Schema::dropIfExists('staff');
    }
};
