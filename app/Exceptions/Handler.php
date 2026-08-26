<?php

namespace App\Exceptions;

use App\Utilities\Common;
use App\Utilities\ErrorMessage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Réponse renvoyée au client pour toute exception non interceptée.
     *
     * Aucun détail technique (requête SQL, code SQLSTATE, trace) ne sort
     * d'ici : seul un message français est retourné, le détail restant
     * disponible dans storage/logs.
     */
    public function render($request, Throwable $e)
    {
        // Réponse déjà construite (FormRequest, abort_response, ...)
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        // Exceptions qui savent se rendre elles-mêmes (ex. JsonResponseException)
        if (method_exists($e, 'render') && $rendered = $e->render($request)) {
            return $rendered;
        }

        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return parent::render($request, $e);
        }

        if ($e instanceof ValidationException) {
            return Common::error(
                $e->validator->errors()->first(),
                $e->validator->errors()
            );
        }

        if ($e instanceof AuthenticationException) {
            return Common::expired();
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return Common::notFound();
        }

        // Toute autre exception (y compris QueryException / PDOException) :
        // le détail technique part dans les logs, le client reçoit un message
        // en français.
        return Common::error(ErrorMessage::report($e), []);
    }
}
