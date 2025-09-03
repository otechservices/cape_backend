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
        Schema::create('capes', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("center_type");
            $table->string("name_pomoter");
            $table->string("email");
            $table->string("phone");
            $table->string("address");
            $table->string("target");
            $table->integer("status");
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
        Schema::dropIfExists('capes');
    }
};
