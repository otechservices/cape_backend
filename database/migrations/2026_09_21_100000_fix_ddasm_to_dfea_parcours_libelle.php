<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige les traces de parcours laissées par la transmission DDASM → DFEA.
 *
 * `transUp` écrivait « Dossier validé transmis au DDASM » aussi bien quand le
 * GUPS transmettait à la DDASM (juste) que quand la DDASM transmettait à la
 * DFEA (faux) : la colonne « Dernière étape » situait alors chez la DDASM un
 * dossier qui attendait la DFEA. Seules les lignes écrites par un compte DDASM
 * sont reprises ; celles du GUPS sont exactes et restent telles quelles.
 */
return new class extends Migration
{
    private const ANCIEN = 'Dossier validé transmis au DDASM';
    private const CORRIGE = 'Dossier validé transmis à la DFEA';

    public function up(): void
    {
        $comptesDdasm = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'ddasm')
            ->where('model_has_roles.model_type', User::class)
            ->select('model_has_roles.model_id');

        DB::table('parcours')
            ->where('libelle', self::ANCIEN)
            ->whereIn('user_id', $comptesDdasm)
            ->update(['libelle' => self::CORRIGE]);
    }

    public function down(): void
    {
        // Rien à restaurer : l'ancien libellé était faux.
    }
};
