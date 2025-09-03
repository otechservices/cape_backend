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
        Schema::table('capes', function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("center_type");
            $table->dropColumn("name_pomoter");
            $table->dropColumn("email");
            $table->dropColumn("phone");
            $table->dropColumn("address");
            $table->dropColumn("target");
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
