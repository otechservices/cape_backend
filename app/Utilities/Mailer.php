<?php

namespace App\Utilities;

use App\Jobs\SendMailJob;
use Illuminate\Support\Facades\Mail;

/**
 * Point d'entrée unique des mails de l'application.
 *
 * sendSimple / sendSimpleWithFile mettent le mail en file (SendMailJob) : la
 * requête HTTP n'attend plus le serveur SMTP, et un serveur en panne n'est plus
 * une erreur pour l'utilisateur mais un job réessayé. Avec
 * QUEUE_CONNECTION=sync, l'envoi reste immédiat comme avant.
 * sendNow envoie réellement ; il est réservé au job.
 */
class Mailer
{
    public static function sendSimple($file, $data, $subject, $name, $email)
    {
        SendMailJob::dispatch($file, $data, $subject, $name, $email);
    }

    /**
     * @param  array  $files  chemins absolus, lus au moment de l'envoi : ils
     *                        doivent exister jusqu'au passage du worker.
     * @return array{success: bool}
     */
    public static function sendSimpleWithFile($file, $data, $subject, $name, $email, $files)
    {
        SendMailJob::dispatch($file, $data, $subject, $name, $email, $files);

        return ['success' => true];
    }

    public static function sendNow($file, $data, $subject, $name, $email, array $files = [])
    {
        Mail::send($file, $data, function ($message) use ($name, $email, $subject, $files) {
            $message->from(config('mail.from.address'), config('mail.from.name'))
                ->subject($subject);
            $message->to($email, $name);
            foreach ($files as $path) {
                $message->attach($path);
            }
        });
    }
}
