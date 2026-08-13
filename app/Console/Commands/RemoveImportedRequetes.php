<?php

namespace App\Console\Commands;

use App\Models\Requete;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Retire définitivement des dossiers importés rattachés à un CPS donné.
 *
 * Cas d'usage : des centres importés comme agréés qui ne le sont pas en réalité
 * (ex. les CAPE de Bembèrèkè, autorisés par le MES comme internats et non agréés
 * par le MASM). On supprime le dossier et toutes ses lignes filles.
 *
 * Le périmètre est volontairement restreint au statut 9 (dossiers issus de
 * l'import) : un dossier réel de la plateforme ne peut pas être supprimé par
 * cette commande.
 */
class RemoveImportedRequetes extends Command
{
    protected $signature = 'requetes:remove
                            {--cps=Bembèrèkè : Filtre sur le nom du CPS de rattachement (sous-chaîne)}
                            {--service= : 1 = CAPE, 2 = Garderie ; vide = les deux}
                            {--force : Supprime réellement (sans cette option, simple simulation)}';

    protected $description = "Supprime des dossiers importés (statut 9) rattachés à un CPS, avec leurs lignes filles";

    /** Tables filles qui référencent requetes (clés étrangères). */
    private const CHILD_TABLES = [
        'capes', 'requete_type_garderies', 'requete_files', 'parcours',
        'reponses', 'avis', 'affectations', 'referals', 'agendas',
    ];

    private function query()
    {
        $cps = $this->option('cps');

        $query = Requete::query()
            ->where('status', Requete::STATUS_AGREE_IMPORTE)
            ->whereHas('district.cps', fn ($q) => $q->where('name', 'like', "%$cps%"));

        if ($this->option('service')) {
            $query->where('service_id', (int) $this->option('service'));
        }

        return $query;
    }

    public function handle()
    {
        $requetes = $this->query()->with('district.cps')->get();

        if ($requetes->isEmpty()) {
            $this->info('Aucun dossier concerné.');

            return 0;
        }

        $this->warn($requetes->count().' dossier(s) seront SUPPRIMÉS définitivement :');
        $this->table(
            ['id', 'code', 'dénomination', 'CPS'],
            $requetes->map(fn ($r) => [
                $r->id, $r->code, mb_strimwidth($r->name, 0, 40, '…'), $r->district?->cps?->name,
            ])->all()
        );

        if (! $this->option('force')) {
            $this->info('Simulation — relancez avec --force pour supprimer.');

            return 0;
        }

        if (! $this->confirm('Confirmer la suppression définitive ?')) {
            $this->info('Annulé.');

            return 0;
        }

        $ids = $requetes->pluck('id');

        DB::transaction(function () use ($ids) {
            // Les lignes filles partent d'abord, sinon leurs clés étrangères
            // bloquent la suppression des dossiers.
            foreach (self::CHILD_TABLES as $table) {
                DB::table($table)->whereIn('requete_id', $ids)->delete();
            }

            Requete::whereIn('id', $ids)->delete();
        });

        $this->info($ids->count().' dossier(s) supprimé(s).');

        return 0;
    }
}
