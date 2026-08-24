<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes de reconnaissance d'un agrément délivré hors plateforme.
 *
 * Deux origines convergent vers cette table, et donc vers le même écran DFEA :
 *  - `import`      : centre déjà agréé, chargé par l'import Excel (statut 9),
 *                    que son promoteur revendique pour reprendre la main dessus ;
 *  - `declaration` : nouveau dossier dont le promoteur déclare, dès
 *                    l'inscription, détenir déjà un agrément.
 *
 * Le code OTP vit ici plutôt que dans `verifications` : cette dernière est
 * indexée sur l'email seul, or un promoteur revendique souvent plusieurs
 * centres à la suite — deux demandes simultanées s'y écraseraient.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agrement_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requete_id')->constrained('requetes');
            $table->foreignId('promoter_id')->constrained('promoters');
            $table->foreignId('user_id')->constrained('users');

            // Promoteur porté par le dossier avant la revendication (le
            // promoteur technique, pour les lignes issues de l'import) : permet
            // de rendre le dossier à son état d'origine si la DFEA refuse.
            $table->foreignId('previous_promoter_id')->nullable()->constrained('promoters');

            // 'import' | 'declaration' : détermine où retombe le dossier en cas
            // de rejet (voir AgrementClaimRepository::decide).
            $table->string('origin')->default('import');

            // 0 = OTP envoyé, 1 = OTP vérifié (complétion du dossier en cours),
            // 2 = soumis à la DFEA, 3 = validé, 4 = rejeté.
            $table->integer('status')->default(0);

            // Le code n'est jamais stocké en clair : seul son hash permet de
            // vérifier la saisie, sans qu'une fuite de la table ne livre les OTP.
            $table->string('otp_hash')->nullable();
            $table->timestamp('otp_expired_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->unsignedInteger('otp_attempts')->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->text('observation')->nullable();

            $table->timestamps();

            // Un centre ne peut être revendiqué que par une demande vivante à la
            // fois ; l'unicité est gérée applicativement (une demande rejetée
            // doit pouvoir être suivie d'une nouvelle), l'index sert la lecture.
            $table->index(['requete_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agrement_claims');
    }
};
