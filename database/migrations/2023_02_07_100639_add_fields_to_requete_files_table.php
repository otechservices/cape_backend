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
        Schema::table('requete_files', function (Blueprint $table) {
            $table->boolean('is_treated')->default(false);
            $table->boolean('is_valid')->default(false);
            $table->text('observation')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requete_files', function (Blueprint $table) {
            //
        });
    }
};
