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
        Schema::create('primestatuts', function (Blueprint $table) {
            $table->id();
            $table->id('statut_id');
            $table->id('prime_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primestatuts');
    }
};
