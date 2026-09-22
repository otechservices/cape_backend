<?php

namespace App\Http\Repositories;

use App\Exceptions\JsonResponseException;
use App\Models\Parcours;
use App\Models\Promoter;
use App\Models\PromoterInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Création d'un compte promoteur à partir du lien reçu par email, et
 * rattachement du centre qui a motivé l'invitation.
 */
class PromoterInvitationRepository
{
    /**
     * Ce que le formulaire de création de compte affiche et pré-remplit.
     */
    public function show(string $token): array
    {
        $invitation = $this->valid($token);

        return [
            'centre' => $invitation->requete?->name,
            'code' => $invitation->requete?->code,
            'email' => $invitation->email,
            'lastname' => $invitation->lastname,
            'firstname' => $invitation->firstname,
            'phone' => $invitation->phone,
            'expires_at' => $invitation->expires_at,
        ];
    }

    /**
     * Crée le promoteur et son compte, puis lui rattache le centre.
     *
     * Le compte est ouvert sous l'adresse à laquelle le lien a été envoyé :
     * sa réception vaut preuve de possession, ce qu'une adresse saisie ici ne
     * garantirait pas.
     */
    public function accept(string $token, array $data): User
    {
        $invitation = $this->valid($token);

        if (User::where('email', $invitation->email)->exists()) {
            $this->fail("Un compte existe déjà avec l'adresse {$invitation->email}. Connectez-vous, puis contactez la DFEA pour que le centre vous soit rattaché.", 409);
        }

        return DB::transaction(function () use ($invitation, $data) {
            // Deux soumissions simultanées du même lien : seule la première passe.
            $verrou = PromoterInvitation::whereKey($invitation->id)->lockForUpdate()->first();
            if ($verrou->isAccepted()) {
                $this->fail('Ce lien a déjà servi : le compte est créé, connectez-vous.', 410);
            }

            $promoter = Promoter::create([
                'lastname' => $data['lastname'],
                'firstname' => $data['firstname'],
                'phone' => $data['phone'],
                'email' => $invitation->email,
            ]);

            $user = User::create([
                'name' => $data['lastname'].' '.$data['firstname'],
                'email' => $invitation->email,
                'promoter_id' => $promoter->id,
                'password' => Hash::make($data['password']),
                // Le mot de passe vient d'être choisi par le promoteur lui-même.
                'is_first_connexion' => false,
            ]);
            $user->assignRole(Role::whereName('Promoteur')->first());

            $invitation->requete->update(['promoter_id' => $promoter->id]);
            $invitation->update(['accepted_at' => now(), 'promoter_id' => $promoter->id]);

            Parcours::create([
                'libelle' => "Centre rattaché au compte promoteur créé sur invitation ({$invitation->email})",
                'requete_id' => $invitation->requete_id,
                'user_id' => $user->id,
            ]);

            return $user;
        });
    }

    private function valid(string $token): PromoterInvitation
    {
        $invitation = PromoterInvitation::with('requete')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if ($invitation === null || $invitation->requete === null) {
            $this->fail('Ce lien de création de compte est invalide.', 404);
        }
        if ($invitation->isAccepted()) {
            $this->fail('Ce lien a déjà servi : le compte est créé, connectez-vous.', 410);
        }
        if ($invitation->isExpired()) {
            $this->fail('Ce lien a expiré (il était valable '.PromoterInvitation::VALIDITE_JOURS.' jours). Contactez la DFEA pour en recevoir un nouveau.', 410);
        }

        return $invitation;
    }

    private function fail(string $message, int $status): void
    {
        throw new JsonResponseException([
            'message' => $message,
            'success' => false,
            'data' => null,
            'warning' => null,
        ], $status);
    }
}
