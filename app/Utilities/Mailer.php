<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Mail;

class Mailer
{
    public static function sendSimple($file, $data, $subject, $name, $email)
    {
        Mail::send($file, $data, function ($message) use ($name, $email, $subject) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject);
            $message->to($email, $name);
        });
    }

    public static function sendSimpleWithFile($file, $data, $subject, $name, $email, $files)
    {

        try {
              Mail::send($file, $data, function ($message) use ($name, $email, $subject, $files) {
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject);
            $message->to($email, $name);
            foreach ($files as $file) {
                $message->attach($file);
            }
        });

        return true;
        } catch (\Throwable $th) {
            return false;
        }
     
    }
}
