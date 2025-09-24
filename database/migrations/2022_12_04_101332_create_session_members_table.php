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
        Schema::create('session_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("session_id");
            $table->foreign("session_id")->references("id")->on("sessions");

            $table->unsignedBigInteger("member_id");
            $table->foreign("member_id")->references("id")->on("members");

            $table->boolean("is_active")->default(false);

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
        Schema::dropIfExists('session_members');
    }
};
