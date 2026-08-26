<?php

namespace App\Utilities;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Traduit une exception en un message utilisateur en français.
 *
 * Objectif : ne JAMAIS renvoyer au client un message technique (requête SQL,
 * code SQLSTATE, nom de table/colonne, trace PHP). Le message d'origine reste
 * disponible via ErrorMessage::technical() pour la journalisation interne.
 */
final class ErrorMessage
{
    /** Message générique retourné quand l'erreur n'est pas identifiable. */
    public const DEFAULT = "Une erreur est survenue lors du traitement de votre demande. Veuillez réessayer.";

    /** Message générique retourné pour toute erreur de base de données. */
    public const DATABASE = "Une erreur est survenue lors de l'enregistrement des données.";

    /**
     * Codes d'erreur MySQL les plus courants et leur équivalent métier.
     */
    private const SQL_CODES = [
        1022 => "Cet enregistrement existe déjà.",
        1048 => "Un champ obligatoire n'a pas été renseigné.",
        1062 => "Cet enregistrement existe déjà.",
        1064 => self::DATABASE,
        1146 => self::DATABASE,
        1054 => self::DATABASE,
        1264 => "Une valeur numérique saisie dépasse les limites autorisées.",
        1265 => "Une valeur saisie n'est pas valide pour ce champ.",
        1364 => "Un champ obligatoire n'a pas été renseigné.",
        1406 => "Une valeur saisie est trop longue.",
        1451 => "Impossible de supprimer cet élément : il est utilisé par d'autres données.",
        1452 => "L'élément sélectionné n'existe pas ou n'est plus disponible.",
        1690 => "Une valeur numérique saisie dépasse les limites autorisées.",
        2002 => "Le service est momentanément indisponible. Veuillez réessayer plus tard.",
        2006 => "Le service est momentanément indisponible. Veuillez réessayer plus tard.",
    ];

    /**
     * Fragments qui trahissent un message technique non destiné à l'utilisateur.
     */
    private const TECHNICAL_MARKERS = [
        'sqlstate', 'sql:', 'select ', 'insert into', 'update ', 'delete from',
        'alter table', 'create table', 'constraint', 'foreign key', 'duplicate entry',
        'integrity constraint', 'no query results', 'connection refused',
        'call to ', 'undefined ', 'trying to access', 'must be of type',
        'stack trace', '.php', '::', 'exception:', 'array to string',
        'attempt to read', 'too few arguments', 'unserialize', 'curl error',
        'class "', 'syntax error',
    ];

    /**
     * Retourne un message sûr, en français, à renvoyer au client.
     */
    public static function of(Throwable $e, ?string $fallback = null): string
    {
        $fallback = $fallback ?: self::DEFAULT;

        if ($e instanceof ValidationException) {
            return $e->validator->errors()->first() ?: "Les informations fournies sont invalides.";
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return self::fromSql($e);
        }

        if ($e instanceof ModelNotFoundException) {
            return "L'élément demandé est introuvable.";
        }

        if ($e instanceof AuthenticationException) {
            return "Vous devez être connecté pour effectuer cette action.";
        }

        if ($e instanceof AuthorizationException) {
            return "Vous n'êtes pas autorisé à effectuer cette action.";
        }

        if ($e instanceof HttpExceptionInterface) {
            return self::fromHttpStatus($e->getStatusCode(), $e->getMessage(), $fallback);
        }

        // Exception métier : on conserve son message s'il est lisible par un utilisateur.
        return self::isSafe($e->getMessage()) ? trim($e->getMessage()) : $fallback;
    }

    /**
     * Journalise le détail technique de l'exception dans les logs applicatifs
     * (storage/logs) et retourne le message français destiné à l'utilisateur.
     *
     * À utiliser partout où le message était auparavant stocké ou affiché tel
     * quel (journal d'activité, réponses API), afin que la trace technique
     * reste disponible sans jamais remonter au client.
     */
    public static function report(Throwable $e, ?string $fallback = null): string
    {
        Log::error(self::technical($e), ['trace' => $e->getTraceAsString()]);

        return self::of($e, $fallback);
    }

    /**
     * Message d'origine complet, réservé aux logs internes.
     */
    public static function technical(Throwable $e): string
    {
        return sprintf(
            '%s: %s in %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }

    /**
     * Un message est considéré comme sûr s'il est court, non vide et ne
     * contient aucun marqueur technique.
     */
    public static function isSafe(?string $message): bool
    {
        $message = trim((string) $message);

        if ($message === '' || mb_strlen($message) > 200) {
            return false;
        }

        $needle = mb_strtolower($message);

        foreach (self::TECHNICAL_MARKERS as $marker) {
            if (str_contains($needle, $marker)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Traduit une erreur de base de données sans jamais exposer la requête.
     */
    private static function fromSql(Throwable $e): string
    {
        $code = null;

        if ($e instanceof QueryException && isset($e->errorInfo[1])) {
            $code = (int) $e->errorInfo[1];
        } elseif ($e instanceof PDOException && isset($e->errorInfo[1])) {
            $code = (int) $e->errorInfo[1];
        }

        return self::SQL_CODES[$code] ?? self::DATABASE;
    }

    /**
     * Traduit un code HTTP en message métier.
     */
    private static function fromHttpStatus(int $status, string $message, string $fallback): string
    {
        return match ($status) {
            400 => "Mauvais paramètre(s) fourni(s).",
            401 => "Vous devez être connecté pour effectuer cette action.",
            403 => "Vous n'êtes pas autorisé à effectuer cette action.",
            404 => "L'élément demandé est introuvable.",
            405 => "Cette action n'est pas autorisée sur cette ressource.",
            409 => "Cette opération entre en conflit avec des données existantes.",
            413 => "Le fichier envoyé est trop volumineux.",
            419 => "Votre session a expiré. Veuillez vous reconnecter.",
            422 => self::isSafe($message) ? trim($message) : "Les informations fournies sont invalides.",
            429 => "Trop de requêtes envoyées. Veuillez patienter un instant.",
            500, 502, 503, 504 => "Le service est momentanément indisponible. Veuillez réessayer plus tard.",
            default => self::isSafe($message) ? trim($message) : $fallback,
        };
    }
}
