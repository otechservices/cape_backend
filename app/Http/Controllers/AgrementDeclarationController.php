<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AgrementDeclarationRepository;
use App\Http\Requests\Requete\DeclareAgreeRequest;
use App\Services\LogService;
use App\Utilities\Common;
use App\Utilities\ErrorMessage;
use Illuminate\Support\Facades\Auth;

class AgrementDeclarationController extends Controller
{
    protected $repository;

    protected $ls;

    public function __construct(AgrementDeclarationRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    /**
     * La DFEA déclare agréé un dossier de la liste « à inscrire en session ».
     */
    public function declare(DeclareAgreeRequest $request)
    {
        if (Auth::user()->roles()->first()?->name !== 'dfea') {
            return response()->json([
                'status' => false,
                'message' => 'Seule la DFEA peut déclarer un centre agréé',
                'data' => null,
            ], 403);
        }

        try {
            $result = $this->repository->declare($request);
            $this->ls->trace([
                'action_name' => "Déclaration d'un centre déjà agréé",
                'description' => "Dossier {$request->id}, agrément n° {$request->aggreement_reference}",
            ]);

            [$message, $warning] = $this->compteRendu($result['notification']);

            return Common::success($message, $result, true, $warning);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /**
     * Ce que la DFEA doit savoir de la notification : à qui elle est partie,
     * ou pourquoi le promoteur n'a pas pu être prévenu.
     *
     * @return array{0: string, 1: ?string}
     */
    private function compteRendu(array $notification): array
    {
        $email = $notification['email'];

        if ($notification['type'] === AgrementDeclarationRepository::NOTIF_AUCUNE) {
            return ['Centre déclaré agréé', "Aucune adresse email valide dans le dossier : le promoteur n'a pas pu être prévenu."];
        }
        if (! $notification['envoye']) {
            return ['Centre déclaré agréé', "Le mail destiné au promoteur ($email) n'a pas pu être envoyé."];
        }
        if ($notification['type'] === AgrementDeclarationRepository::NOTIF_INVITATION) {
            return ["Centre déclaré agréé. Le promoteur n'ayant pas de compte, un lien de création de compte valable 7 jours lui a été envoyé ($email).", null];
        }

        return ["Centre déclaré agréé. Le promoteur en a été informé ($email).", null];
    }
}
