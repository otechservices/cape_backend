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
        Schema::create('activity_reports', function (Blueprint $table) {
            $table->id();
            $table->text("description")->nullable();
            $table->text("activity_report_filename");
            $table->text("financial_report_filename");
            $table->integer("status")->default(0)->comment('0: Nouveau rapport, 1:Rapport validé, 2:Rapport rejeté, 3: Rapport Corrigé');
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
        Schema::dropIfExists('activity_reports');
    }
};
