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
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->longText("description")->nullable();
            $table->integer("status")->default(0)->comment('0:sanction primaire, 1:sanction secondaire');
            $table->text("decret_filename")->nullable();
            $table->date("closed_date")->nullable();
            $table->unsignedBigInteger("cape_id");
            $table->foreign("cape_id")->references("id")->on("capes");
            $table->unsignedBigInteger("type_sanction_id");
            $table->foreign("type_sanction_id")->references("id")->on("type_sanctions");
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
        Schema::dropIfExists('sanctions');
    }
};
