<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

/**
 * Diagnostic de l'envoi de mails, à l'usage de l'administrateur.
 *
 * Les pannes SMTP se manifestaient jusqu'ici au détour d'une autre action
 * (création de compte, récépissé…) avec un message illisible. Ce test les isole
 * en trois étapes, chacune avec son propre message d'erreur :
 *  1. lecture de la configuration effective (celle que Laravel utilise vraiment,
 *     cache de configuration compris) ;
 *  2. connexion et authentification au serveur SMTP, sans rien envoyer ;
 *  3. envoi réel d'un mail à l'adresse saisie.
 */
class MailTestController extends Controller
{
    /** Délai de connexion pour le test, en secondes : mieux vaut un échec rapide qu'une page figée. */
    private const TIMEOUT = 15;

    public function __construct(protected LogService $ls)
    {
    }

    public function test(Request $request)
    {
        if (Auth::user()->roles()->first()?->name !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => "Seul l'administrateur peut tester l'envoi de mails",
                'data' => null,
            ], 403);
        }

        $request->validate(['email' => 'required|email']);

        $mailer = config('mail.default');
        $conf = config("mail.mailers.$mailer", []);
        $from = config('mail.from.address');

        $config = [
            'mailer' => $mailer,
            'transport' => $conf['transport'] ?? null,
            'host' => $conf['host'] ?? null,
            'port' => $conf['port'] ?? null,
            'encryption' => $conf['encryption'] ?? null,
            'username' => $conf['username'] ?? null,
            'password_defined' => !empty($conf['password']),
            'from_address' => $from,
            'from_name' => config('mail.from.name'),
            'config_cached' => app()->configurationIsCached(),
        ];

        $warnings = [];
        if (empty($from)) {
            $warnings[] = "Aucune adresse d'expédition (MAIL_FROM_ADDRESS) : la plupart des serveurs refusent le mail.";
        }
        if (in_array($mailer, ['log', 'array'])) {
            $warnings[] = "Le mailer « $mailer » n'envoie rien : les mails sont seulement journalisés.";
        }
        if (($conf['transport'] ?? null) === 'smtp') {
            if ((int) ($conf['port'] ?? 0) === 465 && ($conf['encryption'] ?? null) !== 'ssl') {
                $warnings[] = "Le port 465 attend en général MAIL_ENCRYPTION=ssl.";
            }
            if (!empty($conf['username']) && empty($conf['password'])) {
                $warnings[] = "Un utilisateur SMTP est défini mais pas de mot de passe.";
            }
        }

        // Mailer reconstruit avec un délai court, pour ce test seulement.
        config(["mail.mailers.$mailer.timeout" => self::TIMEOUT]);
        Mail::purge($mailer);

        $steps = [];

        $transport = Mail::mailer($mailer)->getSymfonyTransport();
        if ($transport instanceof EsmtpTransport) {
            $steps[] = $this->step('Connexion et authentification SMTP', function () use ($transport) {
                $transport->start();
                $transport->stop();
                return 'Serveur joignable, identifiants acceptés';
            });
        } else {
            $steps[] = [
                'step' => 'Connexion et authentification SMTP',
                'ok' => null,
                'message' => "Non applicable : le transport n'est pas SMTP (".get_class($transport).')',
                'ms' => 0,
            ];
        }

        if ($steps[0]['ok'] !== false) {
            $email = $request->email;
            $steps[] = $this->step("Envoi d'un mail de test", function () use ($mailer, $email, $from) {
                Mail::mailer($mailer)->raw(
                    "Ce message confirme que l'application CAPE peut envoyer des mails.\n\nEnvoyé le ".now()->format('d/m/Y à H:i:s').'.',
                    function ($message) use ($email, $from) {
                        $message->to($email)->subject('CAPE — test d\'envoi de mail');
                        if ($from) {
                            $message->from($from, config('mail.from.name'));
                        }
                    }
                );
                return "Mail remis au serveur pour $email. Vérifiez la réception (et les spams).";
            });
        }

        $ok = collect($steps)->every(fn ($s) => $s['ok'] !== false);

        $this->ls->trace([
            'action_name' => 'Test SMTP',
            'description' => ($ok ? 'Réussi' : 'Échec').' vers '.$request->email.' via '.($config['host'] ?? $mailer),
        ]);

        return Common::success(
            $ok ? 'Test réussi' : 'Le test a échoué',
            ['config' => $config, 'steps' => $steps, 'warnings' => $warnings],
            $ok
        );
    }

    private function step(string $name, callable $fn): array
    {
        $start = microtime(true);
        try {
            $message = $fn();
            $ok = true;
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            $ok = false;
        }

        return [
            'step' => $name,
            'ok' => $ok,
            'message' => $message,
            'ms' => (int) round((microtime(true) - $start) * 1000),
        ];
    }
}
