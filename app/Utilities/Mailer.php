<?php
namespace App\Utilities;
use Illuminate\Support\Facades\Mail;
use Log;

class Mailer {

 static public function sendSimple($file, $data, $subject, $name, $email)
    {
        try {
            if (empty($email) || empty($subject)) {
                Log::error('Mail sending failed: Missing required parameters', [
                    'email' => $email,
                    'subject' => $subject
                ]);

                return [
                    'success' => false,
                    'error_code' => 'INVALID_PARAMS',
                    'message' => 'Paramètres manquants pour l\'envoi de l\'email'
                ];
            }

            Mail::send($file, $data, function ($message) use ($name, $email, $subject) {
                $message->from(env('MAIL_FROM_ADDRESS'), env("MAIL_FROM_NAME"))
                    ->subject($subject);
                $message->to($email, $name);
            });

            Log::info('Mail sent successfully', [
                'to' => $email,
                'subject' => $subject
            ]);

            return [
                'success' => true,
                'message' => 'Mail envoyé avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Unexpected mail sending error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $email
            ]);

            return [
                'success' => false,
                'error_code' => 'UNEXPECTED_ERROR',
                'message' => 'Erreur inattendue lors de l\'envoi du mail: ' . $e->getMessage()
            ];
        }
    }
	

  static public function sendSimpleWithFile($file, $data, $subject, $name, $email, $files)
    {
        try {
            if (empty($email) || empty($subject)) {
                Log::error('Mail sending failed: Missing required parameters', [
                    'email' => $email,
                    'subject' => $subject
                ]);

                return [
                    'success' => false,
                    'error_code' => 'INVALID_PARAMS',
                    'message' => 'Paramètres manquants pour l\'envoi de l\'email'
                ];
            }

            Mail::send($file, $data, function ($message) use ($name, $email, $subject, $files) {
                $message->from(env('MAIL_FROM_ADDRESS'), env("MAIL_FROM_NAME"))
                    ->subject($subject);
                $message->to($email, $name);

                if (!empty($files)) {
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            $message->attach($file);
                        } else {
                            Log::warning('Attachment file not found', ['file' => $file]);
                        }
                    }
                }
            });

            Log::info('Mail sent successfully', [
                'to' => $email,
                'subject' => $subject
            ]);

            return [
                'success' => true,
                'message' => 'Mail envoyé avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Unexpected mail sending error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $email
            ]);
            return [
                'success' => false,
                'error_code' => 'UNEXPECTED_ERROR',
                'message' => 'Erreur inattendue lors de l\'envoi du mail: ' . $e->getMessage()
            ];
        }

    }

}
