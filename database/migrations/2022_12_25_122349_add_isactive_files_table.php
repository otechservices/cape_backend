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
        Schema::table('departments', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
        });
        Schema::table('municipalities', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
        });
        Schema::table('districts', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
        });
        Schema::table('type_capes', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
        });
        Schema::table('targets', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
        });
        Schema::table('files', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean("is_active")->default(true);
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
