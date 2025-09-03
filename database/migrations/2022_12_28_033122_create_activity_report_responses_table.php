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
        Schema::create('activity_report_responses', function (Blueprint $table) {
            $table->id();
            $table->longText("instruction");
            $table->unsignedBigInteger("activity_report_id");
            $table->foreign("activity_report_id")->references("id")->on("activity_reports");
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
        Schema::dropIfExists('activity_report_responses');
    }
};
