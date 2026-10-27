<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plusieurs GUPS peuvent désormais couvrir un même arrondissement.
 *
 * Jusqu'ici `districts.cps_id` désignait l'unique GUPS d'un arrondissement, et
 * le GUPS d'un dossier était recalculé à partir de son arrondissement. Avec des
 * couvertures qui se chevauchent, cette déduction n'a plus de réponse unique.
 *
 *  - `cps_district` : couverture complète, un arrondissement pouvant relever
 *    de plusieurs GUPS. (Une prochaine version la descendra au quartier.)
 *  - `districts.cps_id` reste, avec un sens précis : le GUPS par défaut, celui
 *    qui reçoit les nouveaux dépôts. Une seule colonne garantit qu'il n'y en a
 *    qu'un.
 *  - `requetes.cps_id` : le GUPS du dossier, fixé au dépôt et modifié seulement
 *    par un transfert explicite et tracé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cps_district', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cps_id')->constrained('cps')->cascadeOnDelete();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cps_id', 'district_id']);
        });

        DB::statement("
            INSERT INTO cps_district (cps_id, district_id, created_at, updated_at)
            SELECT cps_id, id, NOW(), NOW() FROM districts WHERE cps_id IS NOT NULL
        ");

        Schema::table('requetes', function (Blueprint $table) {
            $table->foreignId('cps_id')->nullable()->after('district_id')->constrained('cps')->nullOnDelete();
        });

        // Dossier en instruction : son GUPS est celui de l'agent qui le détient,
        // pourvu que ce GUPS couvre l'arrondissement. On reprend ainsi les
        // dossiers déjà déplacés à la main sans les renvoyer ailleurs.
        DB::statement("
            UPDATE requetes r
            JOIN affectations a ON a.requete_id = r.id AND a.isLast = 1
            JOIN users u ON u.id = a.user_down
            JOIN cps_district cd ON cd.cps_id = u.cps_id AND cd.district_id = r.district_id
            SET r.cps_id = u.cps_id
            WHERE r.status IN (0, 1, 2, 3, 4)
        ");

        // Tous les autres : le GUPS de leur arrondissement, comme le calcul
        // d'avant cette migration.
        DB::statement("
            UPDATE requetes r
            JOIN districts d ON d.id = r.district_id
            SET r.cps_id = d.cps_id
            WHERE r.cps_id IS NULL AND d.cps_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('requetes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cps_id');
        });
        Schema::dropIfExists('cps_district');
    }
};
