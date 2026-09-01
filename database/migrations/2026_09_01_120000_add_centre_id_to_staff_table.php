<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rattache le personnel à un centre, comme les pensionnaires et les rapports.
 *
 * `staff` ne portait que `promoter_id` : un promoteur gérant plusieurs centres
 * ne pouvait pas dire qui travaille où, et rien ne permettait de subordonner la
 * saisie à l'agrément du centre concerné. On aligne sur `centre_id` vers
 * `requetes`, la clé déjà utilisée par `residents` et `activity_reports`.
 *
 * Reprise des lignes existantes : le rattachement n'est déduit que lorsqu'il est
 * certain, c'est-à-dire quand le promoteur n'a qu'un seul dossier. Les autres
 * restent à NULL, à renseigner par leur promoteur — les inventer serait pire
 * que de les laisser vides.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('centre_id')->nullable()->after('promoter_id')->constrained('requetes');
        });

        DB::statement("
            UPDATE staff s
            JOIN (
                SELECT promoter_id, MIN(id) AS requete_id
                FROM requetes
                WHERE promoter_id IS NOT NULL
                GROUP BY promoter_id
                HAVING COUNT(*) = 1
            ) r ON r.promoter_id = s.promoter_id
            SET s.centre_id = r.requete_id
            WHERE s.centre_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('centre_id');
        });
    }
};
