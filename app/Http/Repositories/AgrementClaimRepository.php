<?php

namespace App\Http\Repositories;

use App\Exceptions\JsonResponseException;
use App\Models\AgrementClaim;
use App\Models\Cape;
use App\Models\File;
use App\Models\Parcours;
use App\Models\Requete;
use App\Models\RequeteFile;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Reconnaissance des agréments délivrés hors plateforme.
 *
 * Deux populations sont concernées, et suivent volontairement le même chemin :
 *
 *  1. Les centres chargés par l'import Excel (statut 9). Ils existent en base
 *     sans compte ni promoteur réel — `promoter_id` pointe sur un promoteur
 *     technique. Leur promoteur crée un compte, revendique le centre, confirme
 *     par OTP, puis dépose l'agrément scanné et les pièces justificatives.
 *
 *  2. Les nouveaux dossiers dont le promoteur déclare, dès l'inscription,
 *     détenir déjà un agrément (voir RequeteController::store).
 *
 * Dans les deux cas la DFEA tranche seule, et le dossier n'emprunte pas le
 * circuit d'instruction complet (CPS, enquête sociale, DDASM, session).
 */
class AgrementClaimRepository
{
    use Repository;

    /** Durée de validité du code de confirmation envoyé par email. */
    private const OTP_TTL_MINUTES = 15;

    /** Au-delà, le code est brûlé : il faut en redemander un. */
    private const OTP_MAX_ATTEMPTS = 5;

    protected $model;

    public function __construct()
    {
        $this->model = app(AgrementClaim::class);
    }

    /**
     * Centres agréés hors plateforme qu'un promoteur peut encore revendiquer.
     *
     * Sont écartés ceux qui font déjà l'objet d'une demande vivante : deux
     * promoteurs ne peuvent pas revendiquer le même centre en parallèle.
     */
    public function available(Request $request)
    {
        return Requete::with(['TypeCape', 'service', 'district.municipality.department', 'district.cps'])
            ->where('status', Requete::STATUS_AGREE_IMPORTE)
            ->whereDoesntHave('agrementClaims', fn ($q) => $q->ongoing())
            ->when($request->search, fn ($q, $term) => $q->where(
                fn ($s) => $s->where('name', 'like', "%$term%")
                    ->orWhere('aggreement_reference', 'like', "%$term%")
                    ->orWhere('town', 'like', "%$term%")
                    ->orWhere('name_pomoter', 'like', "%$term%")
            ))
            ->when($request->service_id, fn ($q, $id) => $q->where('service_id', $id))
            ->when($request->district_id, fn ($q, $id) => $q->where('district_id', $id))
            ->when($request->municipality_id, fn ($q, $id) => $q->whereHas(
                'district', fn ($d) => $d->where('municipality_id', $id)
            ))
            ->when($request->department_id, fn ($q, $id) => $q->whereHas(
                'district.Municipality', fn ($m) => $m->where('department_id', $id)
            ))
            ->orderBy('name')
            ->get();
    }

    /**
     * Ouvre une revendication et envoie le code de confirmation.
     *
     * Le code part sur l'adresse du compte promoteur : les centres importés
     * n'ont, pour la quasi-totalité, aucun email en base. Quand le centre en
     * possède un, il reçoit en parallèle un avis de revendication — c'est le
     * seul canal par lequel un détenteur légitime peut être alerté d'une
     * tentative qui ne viendrait pas de lui. Le contrôle de fond reste celui
     * de la DFEA, sur l'agrément scanné.
     */
    public function requestOtp(Request $request)
    {
        $user = $this->promoterUser();
        $requete = Requete::find($request->requete_id);

        if ($requete === null || $requete->status != Requete::STATUS_AGREE_IMPORTE) {
            $this->fail("Ce centre n'est pas dans la liste des centres agréés régularisables.");
        }

        $ongoing = AgrementClaim::where('requete_id', $requete->id)->ongoing()->latest('id')->first();

        // Une demande ouverte par quelqu'un d'autre, ou déjà transmise, verrouille
        // le centre. En revanche, la demande que l'utilisateur vient lui-même
        // d'ouvrir doit pouvoir resservir : c'est ce qui fait marcher « renvoyer
        // le code », et ce qui évite qu'un email non délivré ne bloque le centre.
        if ($ongoing !== null
            && ($ongoing->user_id != $user->id || $ongoing->status != AgrementClaim::STATUS_OTP_PENDING)) {
            $this->fail('Une demande de régularisation est déjà en cours sur ce centre.');
        }

        $code = (string) random_int(100000, 999999);

        $attributes = [
            'status' => AgrementClaim::STATUS_OTP_PENDING,
            'otp_hash' => Hash::make($code),
            'otp_expired_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'otp_attempts' => 0,
        ];

        if ($ongoing !== null) {
            $ongoing->update($attributes);
            $claim = $ongoing;
        } else {
            $claim = AgrementClaim::create(array_merge([
                'requete_id' => $requete->id,
                'promoter_id' => $user->promoter_id,
                'previous_promoter_id' => $requete->promoter_id,
                'user_id' => $user->id,
                'origin' => AgrementClaim::ORIGIN_IMPORT,
            ], $attributes));
        }

        Mailer::sendSimple(
            'emails.agrement_claim_otp',
            ['code' => $code, 'user' => $user, 'requete' => $requete, 'minutes' => self::OTP_TTL_MINUTES],
            'Code de confirmation - régularisation de centre agréé',
            $user->name,
            $user->email
        );

        // Avis au centre lui-même, uniquement s'il a une adresse connue.
        if (filter_var($requete->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mailer::sendSimple(
                    'emails.agrement_claim_notice',
                    ['requete' => $requete, 'user' => $user],
                    'Demande de régularisation de votre centre',
                    $requete->name,
                    $requete->email
                );
            } catch (\Throwable $th) {
                // L'avis est un filet de sécurité, pas une condition : son échec
                // ne doit pas empêcher le promoteur légitime d'avancer.
                Log::error("Avis de revendication non délivré à {$requete->email} : ".$th->getMessage());
            }
        }

        return $claim;
    }

    /**
     * Confirme le code et rattache le centre au promoteur.
     *
     * Le dossier passe alors au statut « agrément à valider » : il quitte la
     * liste des centres revendicables et apparaît dans l'espace du promoteur,
     * qui peut y déposer ses pièces.
     */
    public function verifyOtp(Request $request)
    {
        $user = Auth::user();

        $claim = AgrementClaim::where('requete_id', $request->requete_id)
            ->where('user_id', $user->id)
            ->where('status', AgrementClaim::STATUS_OTP_PENDING)
            ->latest('id')
            ->first();

        if ($claim === null) {
            $this->fail('Aucune demande de régularisation en attente de confirmation pour ce centre.');
        }

        if ($claim->otp_expired_at !== null && $claim->otp_expired_at->isPast()) {
            $this->fail('Ce code a expiré. Veuillez en demander un nouveau.');
        }

        if ($claim->otp_attempts >= self::OTP_MAX_ATTEMPTS) {
            $this->fail('Trop de tentatives sur ce code. Veuillez en demander un nouveau.');
        }

        if (! Hash::check((string) $request->code, (string) $claim->otp_hash)) {
            $claim->increment('otp_attempts');
            $this->fail('Code de confirmation incorrect.');
        }

        return DB::transaction(function () use ($claim, $user) {
            $claim->update([
                'status' => AgrementClaim::STATUS_VERIFIED,
                'otp_verified_at' => now(),
                'otp_hash' => null,
            ]);

            $claim->requete->update([
                'status' => Requete::STATUS_AGREMENT_A_VALIDER,
                'promoter_id' => $user->promoter_id,
                'claimed_by' => $user->id,
                'claimed_at' => now(),
            ]);

            Parcours::create([
                'libelle' => 'Centre revendiqué par son promoteur pour régularisation',
                'requete_id' => $claim->requete_id,
                'user_id' => $user->id,
            ]);

            return $claim->fresh(['requete']);
        });
    }

    /**
     * Pièces attendues pour la régularisation, avec l'état de chacune.
     *
     * Le référentiel est celui du circuit normal (`files` du service concerné) :
     * un centre régularisé doit présenter le même dossier numérique qu'un centre
     * instruit sur la plateforme, faute de quoi les contrôles ultérieurs
     * porteraient sur un dossier vide.
     */
    public function requiredFiles(Request $request)
    {
        $requete = $this->authorizedRequete($request->requete_id);

        $deposited = RequeteFile::where('requete_id', $requete->id)
            ->where('level', 0)
            ->whereNotNull('file_id')
            ->get()
            ->keyBy('file_id');

        return File::with('TypeFile')
            ->where('service_id', $requete->service_id)
            ->where('is_active', true)
            ->get()
            ->map(fn ($file) => [
                'file_id' => $file->id,
                'name' => $file->name,
                'description' => $file->description,
                'is_required' => (bool) $file->is_required,
                'filename' => $deposited[$file->id]->filename ?? null,
                'is_setted' => isset($deposited[$file->id]),
            ])
            ->values();
    }

    /**
     * Dépose ou remplace une pièce du dossier de régularisation.
     */
    public function addFile(Request $request)
    {
        $requete = $this->authorizedRequete($request->requete_id);

        $filename = FileStorage::setFile(
            'doc_store',
            $request->file('file'),
            $requete->code,
            Str::slug($request->reference ?? 'piece').'-'.time()
        );

        $existing = RequeteFile::where('requete_id', $requete->id)
            ->where('level', 0)
            ->where('file_id', $request->file_id)
            ->first();

        if ($existing) {
            $existing->update(['filename' => $filename, 'is_treated' => false, 'is_valid' => false]);

            return $existing;
        }

        return RequeteFile::create([
            'type' => 'PDF',
            'reference' => $request->reference,
            'filename' => $filename,
            'level' => 0,
            'file_id' => $request->file_id,
            'requete_id' => $requete->id,
            'promoter_id' => Auth::user()->promoter_id,
        ]);
    }

    /**
     * Transmet le dossier régularisé à la DFEA.
     *
     * Le contrôle de complétude est fait ici et non côté navigateur : c'est la
     * seule barrière qu'un dossier incomplet ne peut pas contourner.
     */
    public function submit(Request $request)
    {
        $requete = $this->authorizedRequete($request->requete_id);

        $claim = AgrementClaim::where('requete_id', $requete->id)
            ->where('status', AgrementClaim::STATUS_VERIFIED)
            ->latest('id')
            ->first();

        if ($claim === null) {
            $this->fail("Cette demande n'est pas en cours de complétion.");
        }

        $reference = trim((string) $request->aggreement_reference);
        if ($reference === '') {
            $this->fail("La référence de l'agrément est obligatoire.");
        }

        $agreementFile = $requete->file_aggreement;
        if ($request->file('file_aggreement')) {
            $agreementFile = FileStorage::setFile(
                'doc_store', $request->file('file_aggreement'), $requete->code, 'agrement-'.time()
            );
        }

        if ($agreementFile === null) {
            $this->fail("Le scan de l'agrément est obligatoire.");
        }

        $missing = $this->missingRequiredFiles($requete);
        if ($missing !== []) {
            $this->fail('Pièces obligatoires manquantes : '.implode(', ', $missing));
        }

        return DB::transaction(function () use ($claim, $requete, $reference, $request, $agreementFile) {
            $requete->update([
                'aggreement_reference' => $reference,
                'aggreement_year' => $request->aggreement_year,
                'file_aggreement' => $agreementFile,
                'status' => Requete::STATUS_AGREMENT_A_VALIDER,
            ]);

            $claim->update([
                'status' => AgrementClaim::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            Parcours::create([
                'libelle' => 'Dossier de régularisation transmis à la DFEA',
                'requete_id' => $requete->id,
                'user_id' => Auth::id(),
            ]);

            return $claim->fresh(['requete']);
        });
    }

    /**
     * Les demandes du promoteur connecté, tous centres confondus.
     */
    public function mine()
    {
        return AgrementClaim::with([
            'requete.TypeCape', 'requete.service', 'requete.district.municipality.department',
            'requete.files.file',
        ])
            ->where('promoter_id', Auth::user()->promoter_id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * File d'attente de la DFEA : les dossiers transmis, quelle qu'en soit
     * l'origine — centre importé revendiqué ou agrément déclaré à l'inscription.
     */
    public function pending(Request $request)
    {
        $this->assertDfea();

        return AgrementClaim::with([
            'requete.TypeCape', 'requete.service', 'requete.district.municipality.department',
            'requete.district.cps', 'requete.files.file.TypeFile', 'promoter', 'user',
        ])
            ->where('status', AgrementClaim::STATUS_SUBMITTED)
            ->when($request->service_id, fn ($q, $id) => $q->whereHas(
                'requete', fn ($r) => $r->where('service_id', $id)
            ))
            ->orderBy('submitted_at')
            ->get();
    }

    /**
     * Décision de la DFEA.
     *
     * En cas d'acceptation, le dossier rejoint l'état d'un dossier autorisé par
     * le circuit normal : ligne `capes`, compte d'accès du centre, agrément PDF.
     *
     * En cas de refus, le dossier retombe là où il a un sens :
     *  - un centre importé redevient un agréé hors plateforme (statut 9), rendu
     *    à son promoteur technique — il reste agréé, seule la revendication est
     *    écartée ;
     *  - un dossier déclaré bascule dans le circuit d'instruction complet
     *    (statut 0), sans que le promoteur ait à redéposer sa demande.
     */
    public function decide(Request $request)
    {
        $this->assertDfea();

        $claim = AgrementClaim::with('requete')->find($request->id);

        if ($claim === null || $claim->status != AgrementClaim::STATUS_SUBMITTED) {
            $this->fail("Cette demande n'est pas en attente de décision.");
        }

        return DB::transaction(function () use ($claim, $request) {
            return $request->boolean('decision')
                ? $this->approve($claim, $request->observation)
                : $this->reject($claim, $request->observation);
        });
    }

    /**
     * Matérialise l'agrément reconnu : le centre devient un CAPE/Garderie
     * autorisé, exactement comme à l'issue d'une session d'agrément.
     */
    private function approve(AgrementClaim $claim, ?string $observation): AgrementClaim
    {
        $requete = $claim->requete;

        $requete->update([
            'status' => Requete::STATUS_AUTORISE,
            'has_agreemant' => true,
            'is_authorized' => true,
            'is_validated' => true,
            'can_closed' => true,
            'final_observation' => $observation,
        ]);

        $cape = Cape::firstOrCreate(['requete_id' => $requete->id], ['status' => 1]);

        $this->ensureCapeAccount($requete, $cape);

        $claim->update([
            'status' => AgrementClaim::STATUS_VALIDATED,
            'decided_at' => now(),
            'decided_by' => Auth::id(),
            'observation' => $observation,
        ]);

        Parcours::create([
            'libelle' => 'Agrément reconnu par la DFEA',
            'requete_id' => $requete->id,
            'user_id' => Auth::id(),
        ]);

        return $claim->fresh(['requete']);
    }

    private function reject(AgrementClaim $claim, ?string $observation): AgrementClaim
    {
        $requete = $claim->requete;

        if ($claim->origin === AgrementClaim::ORIGIN_IMPORT) {
            $requete->update([
                'status' => Requete::STATUS_AGREE_IMPORTE,
                'promoter_id' => $claim->previous_promoter_id ?? $requete->promoter_id,
                'claimed_by' => null,
                'claimed_at' => null,
            ]);
            $libelle = 'Revendication du centre écartée par la DFEA';
        } else {
            // Le dossier n'est pas perdu : il repart au début du circuit
            // d'instruction, avec les pièces déjà déposées.
            $requete->update([
                'status' => 0,
                'has_agreemant' => null,
                'is_authorized' => null,
            ]);
            $libelle = "Agrément déclaré non reconnu : le dossier suit le circuit d'instruction";
        }

        $claim->update([
            'status' => AgrementClaim::STATUS_REJECTED,
            'decided_at' => now(),
            'decided_by' => Auth::id(),
            'observation' => $observation,
        ]);

        Parcours::create([
            'libelle' => $libelle,
            'requete_id' => $requete->id,
            'user_id' => Auth::id(),
        ]);

        $this->notifyPromoter($claim, $requete, false, $observation);

        return $claim->fresh(['requete']);
    }

    /**
     * Crée le compte d'accès du centre s'il n'en a pas encore, et lui transmet
     * son agrément. Le compte est indexé sur la ligne `capes` : un centre
     * importé qui en possédait déjà une n'en obtient pas un second.
     */
    private function ensureCapeAccount(Requete $requete, Cape $cape): void
    {
        $filePath = $this->generateAgrementPdf($requete);

        $existing = User::where('cape_id', $cape->id)->first();

        // Sans adresse exploitable, le compte ne peut être ni créé ni transmis :
        // la DFEA le fera depuis la gestion des utilisateurs.
        $email = filter_var($requete->email, FILTER_VALIDATE_EMAIL)
            ? $requete->email
            : ($requete->email_pomoter ?: $requete->email_chief);

        if ($existing !== null || ! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
            $this->notifyPromoter(null, $requete, true, null, $filePath === null ? [] : [$filePath]);

            return;
        }

        $password = Str::random(8);
        $user = User::create([
            'code' => Str::uuid(),
            'name' => trim($requete->name_pomoter.' '.$requete->firstname_pomoter) ?: $requete->name,
            'email' => $email,
            'password' => Hash::make($password),
            'cape_id' => $cape->id,
        ]);
        $user->assignRole(Role::whereName('cape')->first());

        try {
            $data = ['name' => $requete->name, 'cape' => $requete, 'password' => $password];
            $filePath === null
                ? Mailer::sendSimple('emails.cape_account', $data, "Compte d'accès CAPE", $requete->name, $email)
                : Mailer::sendSimpleWithFile('emails.cape_account', $data, "Compte d'accès CAPE", $requete->name, $email, [$filePath]);
        } catch (\Throwable $th) {
            Log::error("Compte CAPE créé mais mail non délivré à $email : ".$th->getMessage());
        }
    }

    /**
     * Produit l'agrément PDF et l'attache au dossier.
     *
     * La décision de la DFEA est déjà actée en base au moment où l'on passe ici,
     * dans la même transaction : un incident d'écriture ne doit donc pas la
     * faire disparaître. En cas d'échec, on renonce au PDF — et à la ligne
     * `requete_files` qui pointerait sur un fichier absent — plutôt qu'à
     * l'agrément lui-même.
     *
     * @return string|null Chemin du PDF produit, null si la génération a échoué.
     */
    private function generateAgrementPdf(Requete $requete): ?string
    {
        $recFile = time().'agrement.pdf';
        $directory = public_path('docs/'.$requete->code);
        $filePath = $directory.'/'.$recFile;

        try {
            // Un centre importé n'a pu créer son répertoire qu'en déposant ses
            // pièces ; on ne présume pas qu'il existe.
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            Pdf::loadView('emails.agrement_pj', ['name' => $requete->name])->save($filePath);

            RequeteFile::create([
                'type' => 'PDF',
                'reference' => "Agrément d'autorisation",
                'filename' => $recFile,
                'level' => 1,
                'file_id' => null,
                'requete_id' => $requete->id,
            ]);

            return $filePath;
        } catch (\Throwable $th) {
            Log::error("Agrément PDF non généré pour {$requete->code} : ".$th->getMessage());

            return null;
        }
    }

    /**
     * Informe le promoteur de la décision. L'échec d'envoi n'annule jamais la
     * décision : elle est déjà actée en base et lisible depuis son espace.
     */
    private function notifyPromoter(?AgrementClaim $claim, Requete $requete, bool $approved, ?string $observation, array $files = []): void
    {
        $email = $requete->email_pomoter ?: $requete->email;
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $data = ['requete' => $requete, 'approved' => $approved, 'observation' => $observation];
        $subject = $approved ? 'Agrément reconnu' : 'Agrément non reconnu';

        try {
            $files === []
                ? Mailer::sendSimple('emails.agrement_claim_decision', $data, $subject, $requete->name_pomoter ?: $requete->name, $email)
                : Mailer::sendSimpleWithFile('emails.agrement_claim_decision', $data, $subject, $requete->name_pomoter ?: $requete->name, $email, $files);
        } catch (\Throwable $th) {
            Log::error("Notification de décision non délivrée à $email : ".$th->getMessage());
        }
    }

    /**
     * Libellés des pièces obligatoires encore absentes du dossier.
     *
     * @return string[]
     */
    private function missingRequiredFiles(Requete $requete): array
    {
        $deposited = RequeteFile::where('requete_id', $requete->id)
            ->where('level', 0)
            ->whereNotNull('file_id')
            ->pluck('file_id')
            ->all();

        return File::where('service_id', $requete->service_id)
            ->where('is_active', true)
            ->where('is_required', true)
            ->whereNotIn('id', $deposited)
            ->pluck('name')
            ->all();
    }

    /**
     * Le dossier visé doit appartenir au promoteur connecté et être en cours de
     * régularisation : sans ce garde-fou, l'identifiant passé en paramètre
     * suffirait à déposer des pièces sur le dossier d'autrui.
     */
    private function authorizedRequete($requeteId): Requete
    {
        $user = $this->promoterUser();
        $requete = Requete::find($requeteId);

        if ($requete === null || $requete->promoter_id != $user->promoter_id) {
            $this->fail("Ce dossier ne fait pas partie de vos centres.");
        }

        if ($requete->status != Requete::STATUS_AGREMENT_A_VALIDER) {
            $this->fail("Ce dossier n'est pas en cours de régularisation.");
        }

        return $requete;
    }

    /**
     * L'utilisateur connecté, garanti rattaché à un profil promoteur : sans lui,
     * la demande n'aurait personne à qui rattacher le centre.
     */
    private function promoterUser()
    {
        $user = Auth::user();

        if ($user === null || $user->promoter_id === null) {
            $this->fail('Seul un compte promoteur peut revendiquer un centre agréé.');
        }

        return $user;
    }

    private function assertDfea(): void
    {
        $role = Auth::user()->roles()->first()?->name;

        if (! in_array($role, ['dfea', 'admin', 'Administrateur'], true)) {
            $this->fail("Seule la DFEA peut statuer sur les agréments existants.");
        }
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
