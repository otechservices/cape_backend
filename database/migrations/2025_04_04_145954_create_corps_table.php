<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        Schema::create('corps', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('libelle');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corps');
    }
};
