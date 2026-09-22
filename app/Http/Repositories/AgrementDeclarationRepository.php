<?php

namespace App\Http\Repositories;

use App\Exceptions\JsonResponseException;
use App\Models\Cape;
use App\Models\Parcours;
use App\Models\PromoterInvitation;
use App\Models\Requete;
use App\Models\User;
use App\Services\AgrementPdfService;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Déclaration directe, par la DFEA, d'un centre déjà agréé.
 *
 * Depuis la liste « à inscrire en session », la DFEA peut constater qu'un
 * centre détient déjà son agrément : il n'a plus à passer en session. Le
 * dossier est placé dans l'état exact d'un agrément reconnu hors plateforme
 * (voir AgrementClaimRepository::approve), puis le promoteur est invité à
 * compléter les données du centre — ou, s'il n'a pas de compte, à en créer un.
 */
class AgrementDeclarationRepository
{
    /** Le promoteur a un compte : il est invité à compléter les données du centre. */
    public const NOTIF_COMPTE = 'compte';

    /** Pas de compte : il reçoit un lien de création de compte. */
    public const NOTIF_INVITATION = 'invitation';

    /** Aucune adresse exploitable : personne n'a pu être prévenu. */
    public const NOTIF_AUCUNE = 'aucune';

    /** Statut des dossiers de la liste « à inscrire en session ». */
    private const STATUS_A_INSCRIRE = 7;

    /**
     * @return array{requete: Requete, notification: array{type: string, email: ?string, envoye: bool}}
     */
    public function declare(Request $request): array
    {
        $requete = Requete::with('promoter.user')->findOrFail($request->id);

        if ($requete->status != self::STATUS_A_INSCRIRE || $requete->session_id !== null) {
            $this->fail('Seul un dossier à inscrire en session peut être déclaré agréé depuis cette liste.');
        }

        $scan = FileStorage::setFile('doc_store', $request->file('file_aggreement'), $requete->code, time());

        DB::transaction(function () use ($requete, $request, $scan) {
            $donnees = [
                'status' => Requete::STATUS_AUTORISE,
                'has_agreemant' => true,
                'is_authorized' => true,
                'is_validated' => true,
                'can_closed' => true,
                'aggreement_reference' => $request->aggreement_reference,
                'aggreement_year' => $request->aggreement_year,
                'file_aggreement' => $scan,
            ];
            if (filled($request->observation)) {
                $donnees['final_observation'] = $request->observation;
            }
            $requete->update($donnees);

            Cape::firstOrCreate(['requete_id' => $requete->id], ['status' => 1]);

            Parcours::create([
                'libelle' => "Centre déclaré déjà agréé par la DFEA (agrément n° {$request->aggreement_reference})",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        });

        $pdf = AgrementPdfService::generate($requete);

        return [
            'requete' => $requete->fresh(),
            'notification' => $this->notifyPromoter($requete, $pdf),
        ];
    }

    /**
     * Prévient le promoteur que l'agrément de son centre est confirmé et
     * l'invite à renseigner personnel, pensionnaires et rapports d'activité.
     * Sans compte, le mail porte le lien de création de compte.
     *
     * L'échec d'envoi n'annule jamais la déclaration, déjà actée en base : il
     * est seulement remonté à la DFEA.
     *
     * @return array{type: string, email: ?string, envoye: bool}
     */
    private function notifyPromoter(Requete $requete, ?string $pdf): array
    {
        $user = $this->promoterAccount($requete);
        $invitation = null;
        $token = null;

        if ($user !== null) {
            $type = self::NOTIF_COMPTE;
            $email = $user->email;
            $nom = $user->name;
        } else {
            $email = collect([$requete->email_pomoter, $requete->email])
                ->first(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));
            if ($email === null) {
                return ['type' => self::NOTIF_AUCUNE, 'email' => null, 'envoye' => false];
            }

            $type = self::NOTIF_INVITATION;
            $nom = trim($requete->name_pomoter.' '.$requete->firstname_pomoter) ?: $requete->name;
            [$invitation, $token] = PromoterInvitation::issue([
                'requete_id' => $requete->id,
                'email' => $email,
                'lastname' => $requete->name_pomoter,
                'firstname' => $requete->firstname_pomoter,
                'phone' => $requete->phone_pomoter,
                'created_by' => Auth::id(),
            ]);
        }

        $data = [
            'requete' => $requete,
            'nom' => $nom,
            'invitationUrl' => $token === null ? null : PromoterInvitation::url($token),
            'expiration' => $invitation?->expires_at,
            'espaceUrl' => env('APP_FRONT_URL').'/promoter',
            'pieceJointe' => $pdf !== null,
        ];
        $sujet = "Confirmation de l'agrément de votre centre";

        try {
            $pdf === null
                ? Mailer::sendSimple('emails.agrement_declare', $data, $sujet, $nom, $email)
                : Mailer::sendSimpleWithFile('emails.agrement_declare', $data, $sujet, $nom, $email, [$pdf]);
            $envoye = true;
        } catch (\Throwable $th) {
            Log::error("Déclaration d'agrément de {$requete->code} : mail non délivré à $email : ".$th->getMessage());
            $envoye = false;
        }

        return ['type' => $type, 'email' => $email, 'envoye' => $envoye];
    }

    /**
     * Compte du promoteur du centre. À défaut, un compte promoteur déjà ouvert
     * sous l'adresse du promoteur déclarée dans le dossier : le centre y est
     * rattaché plutôt que d'inviter la même personne à ouvrir un second compte.
     */
    private function promoterAccount(Requete $requete): ?User
    {
        if ($requete->promoter?->user !== null) {
            return $requete->promoter->user;
        }

        if (! filter_var($requete->email_pomoter, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $user = User::where('email', $requete->email_pomoter)->whereNotNull('promoter_id')->first();
        if ($user !== null) {
            $requete->update(['promoter_id' => $user->promoter_id]);
            Parcours::create([
                'libelle' => "Centre rattaché au compte promoteur existant ({$user->email})",
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);
        }

        return $user;
    }

    private function fail(string $message): void
    {
        throw new JsonResponseException([
            'message' => $message,
            'success' => false,
            'data' => null,
            'warning' => null,
        ], 422);
    }
}
