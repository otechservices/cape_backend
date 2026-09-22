<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PromoterInvitationRepository;
use App\Http\Requests\PromoterInvitation\AcceptPromoterInvitationRequest;
use App\Utilities\Common;
use App\Utilities\ErrorMessage;

/**
 * Routes publiques : le promoteur invité n'a, par définition, pas encore de
 * compte. Le jeton du lien tient lieu d'authentification.
 */
class PromoterInvitationController extends Controller
{
    protected $repository;

    public function __construct(PromoterInvitationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function show(string $token)
    {
        try {
            return Common::success('Invitation valide', $this->repository->show($token));
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }

    public function accept(AcceptPromoterInvitationRequest $request, string $token)
    {
        try {
            $this->repository->accept($token, $request->validated());

            return Common::success('Compte créé : le centre vous est rattaché. Vous pouvez vous connecter.', null);
        } catch (\App\Exceptions\JsonResponseException $e) {
            return $e->render();
        } catch (\Throwable $th) {
            return Common::error(ErrorMessage::of($th), []);
        }
    }
}
