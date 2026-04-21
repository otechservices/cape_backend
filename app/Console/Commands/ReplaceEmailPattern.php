<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReplaceEmailPattern extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:replace-pattern
                            {--old-pattern=cps : Ancien pattern à remplacer dans les emails}
                            {--new-pattern=gups : Nouveau pattern de remplacement}
                            {--table=users : Table à mettre à jour}
                            {--column=email : Colonne contenant les emails}
                            {--dry-run : Simuler sans modifier la base de données}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remplace un pattern dans les adresses email (ex: cps → gup dans masm.cpsmenontin@gmail.com)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $oldPattern = $this->option('old-pattern');
        $newPattern = $this->option('new-pattern');
        $table      = $this->option('table');
        $column     = $this->option('column');
        $dryRun     = $this->option('dry-run');

        $this->info("=== Remplacement d'email ===");
        $this->line("  Table     : <fg=cyan>{$table}</>");
        $this->line("  Colonne   : <fg=cyan>{$column}</>");
        $this->line("  Ancien    : <fg=red>{$oldPattern}</>");
        $this->line("  Nouveau   : <fg=green>{$newPattern}</>");
        $this->line("  Mode      : <fg=yellow>" . ($dryRun ? 'Simulation (dry-run)' : 'Réel') . "</>");
        $this->newLine();

        // Récupérer les emails correspondants
        $records = DB::table($table)
            ->where($column, 'LIKE', "%{$oldPattern}%")
            ->select('id', $column)
            ->get();

        if ($records->isEmpty()) {
            $this->warn("Aucun email contenant \"{$oldPattern}\" trouvé dans {$table}.{$column}.");
            return self::SUCCESS;
        }

        // Afficher un aperçu
        $this->info("Emails concernés ({$records->count()}) :");
        $rows = $records->map(fn($r) => [
            $r->id,
            $r->{$column},
            str_replace($oldPattern, $newPattern, $r->{$column}),
        ])->toArray();

        $this->table(['ID', 'Avant', 'Après'], $rows);

        // Confirmation si pas en dry-run
        if (!$dryRun) {
            if (!$this->confirm("Voulez-vous appliquer ces {$records->count()} modification(s) ?")) {
                $this->warn('Opération annulée.');
                return self::SUCCESS;
            }
        }

        if ($dryRun) {
            $this->info('Mode dry-run : aucune modification effectuée.');
            return self::SUCCESS;
        }

        // Appliquer les modifications
        $updated = 0;
        foreach ($records as $record) {
            $newEmail = str_replace($oldPattern, $newPattern, $record->{$column});
            DB::table($table)
                ->where('id', $record->id)
                ->update([$column => $newEmail]);
            $updated++;
        }

        $this->newLine();
        $this->info("<fg=green>✔ {$updated} email(s) mis à jour avec succès.</>");

        return self::SUCCESS;
    }
}