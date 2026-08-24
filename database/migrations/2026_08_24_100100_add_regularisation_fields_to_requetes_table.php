<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traçabilité de la reprise en main d'un dossier par son promoteur.
 *
 * Les 218 dossiers issus de l'import portent tous `promoter_id = 1`, promoteur
 * technique : sans ces colonnes, rien ne distinguerait un dossier réellement
 * revendiqué d'un dossier encore orphelin une fois `promoter_id` réécrit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->foreignId('claimed_by')->nullable()->constrained('users');
            $table->timestamp('claimed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('claimed_by');
            $table->dropColumn('claimed_at');
        });
    }
};
