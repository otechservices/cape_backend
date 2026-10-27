<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace la clôture des sessions, et en particulier la clôture forcée.
 *
 * Jusqu'ici une session ne se fermait que si tous ses dossiers avaient reçu une
 * décision. La DFEA peut désormais la fermer de force, avec un motif, en
 * choisissant le sort des dossiers :
 *  - `forced_continue` : les décisions prises poursuivent le workflow, les
 *    dossiers sans décision retournent dans la liste « à inscrire en session » ;
 *  - `forced_cancel` : la session est annulée, aucune de ses décisions n'est
 *    retenue et tous ses dossiers sont à réinscrire.
 * `normal` désigne la clôture habituelle. NULL : session pas encore close, ou
 * close avant cette migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('closure_mode')->nullable()->after('is_active');
            $table->text('closure_reason')->nullable()->after('closure_mode');
            $table->foreignId('closed_by')->nullable()->after('closure_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable()->after('closed_by');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by');
            $table->dropColumn(['closure_mode', 'closure_reason', 'closed_at']);
        });
    }
};
