<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::table('logs', function (Blueprint $table) {
           $table->unsignedBigInteger('dony_by')->nullable()->change();

        });

         Schema::table('actualities', function (Blueprint $table) {
           $table->enum('category',['CAPE','GARDERIE']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
