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
        Schema::create('billing_responses', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->unsignedBigInteger("billing_id");
            $table->foreign("billing_id")->references("id")->on("billings");
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
        Schema::dropIfExists('billing_responses');
    }
};
