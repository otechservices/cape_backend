<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AgrementClaimRepository;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use App\Utilities\ErrorMessage;

/**
 * Reconnaissance des agréments délivrés hors plateforme.
 *
 * Côté promoteur : recherche d'un centre agréé importé, revendication confirmée
 * par code, dépôt des pièces, transmission.
 * Côté DFEA : file d'attente et décision.
 *
 * @see \App\Http\Repositories\AgrementClaimRepository
 */
class AgrementClaimController extends Controller
{
    protected $repository;

    protected $ls;

    public function __construct(AgrementClaimRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    /** Centres agréés hors plateforme encore revendicables. */
    public function available(Request $request)
    {
        $message = 'Liste des centres agréés régularisables';

        try {
            $result = $this->repository->available($request);
            $this->ls->trace(['action_name' => $message, 'description' => 'Recherche : '.$request->search]);

            return Common::success($message, $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Ouvre une revendication et envoie le code de confirmation par email. */
    public function requestOtp(Request $request)
    {
        $message = 'Demande de régularisation de centre agréé';

        try {
            $result = $this->repository->requestOtp($request);
            $this->ls->trace(['action_name' => $message, 'description' => 'Centre : '.$request->requete_id]);

            return Common::success('Un code de confirmation vous a été envoyé par email', $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Confirme le code et rattache le centre au promoteur. */
    public function verifyOtp(Request $request)
    {
        $message = 'Confirmation de revendication de centre agréé';

        try {
            $result = $this->repository->verifyOtp($request);
            $this->ls->trace(['action_name' => $message, 'description' => 'Centre : '.$request->requete_id]);

            return Common::success('Centre rattaché à votre compte avec succès', $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Pièces attendues pour la régularisation, avec l'état de chacune. */
    public function requiredFiles(Request $request)
    {
        $message = 'Pièces attendues pour la régularisation';

        try {
            $result = $this->repository->requiredFiles($request);

            return Common::success($message, $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Dépose ou remplace une pièce du dossier de régularisation. */
    public function addFile(Request $request)
    {
        $message = 'Dépôt de pièce pour régularisation';

        try {
            $result = $this->repository->addFile($request);
            $this->ls->trace(['action_name' => $message, 'description' => 'Centre : '.$request->requete_id]);

            return Common::successCreate('Pièce enregistrée avec succès', $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Transmet le dossier régularisé à la DFEA. */
    public function submit(Request $request)
    {
        $message = 'Transmission du dossier de régularisation';

        try {
            $result = $this->repository->submit($request);
            $this->ls->trace(['action_name' => $message, 'description' => 'Centre : '.$request->requete_id]);

            return Common::success('Dossier transmis à la DFEA pour validation', $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Les demandes du promoteur connecté. */
    public function mine()
    {
        $message = 'Mes demandes de régularisation';

        try {
            $result = $this->repository->mine();

            return Common::success($message, $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** File d'attente de la DFEA. */
    public function pending(Request $request)
    {
        $message = 'Agréments existants en attente de validation';

        try {
            $result = $this->repository->pending($request);

            return Common::success($message, $result);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    /** Décision de la DFEA sur un agrément existant. */
    public function decide(Request $request)
    {
        $message = 'Décision sur un agrément existant';

        try {
            $result = $this->repository->decide($request);
            $this->ls->trace([
                'action_name' => $message,
                'description' => 'Demande '.$request->id.' : '.($request->boolean('decision') ? 'reconnu' : 'non reconnu'),
            ]);

            return Common::success(
                $request->boolean('decision') ? 'Agrément reconnu avec succès' : 'Agrément non reconnu',
                $result
            );
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }
}
