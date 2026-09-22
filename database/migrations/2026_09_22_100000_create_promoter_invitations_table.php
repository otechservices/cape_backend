<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invitations à créer un compte promoteur.
 *
 * Quand la DFEA déclare agréé un centre dont le promoteur n'a pas de compte,
 * celui-ci reçoit un lien de création de compte valable 7 jours. Le compte créé
 * par ce lien se voit rattacher le centre : c'est ce qui lui ouvre la saisie du
 * personnel, des pensionnaires et des rapports d'activité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoter_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requete_id')->constrained('requetes')->cascadeOnDelete();

            // Adresse à laquelle le lien a été envoyé : le compte sera créé sous
            // cette adresse, dont la possession est prouvée par la réception du lien.
            $table->string('email');

            // Pré-remplissage du formulaire, repris du dossier.
            $table->string('lastname')->nullable();
            $table->string('firstname')->nullable();
            $table->string('phone')->nullable();

            // Le jeton n'est jamais stocké en clair : seul son hash SHA-256
            // permet de retrouver l'invitation, sans qu'une fuite de la table
            // ne livre des liens utilisables.
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('promoter_id')->nullable()->constrained('promoters');
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_invitations');
    }
};
