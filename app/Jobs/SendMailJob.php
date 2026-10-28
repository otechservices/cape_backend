<?php

namespace App\Jobs;

use App\Utilities\Mailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Log;

/**
 * Envoi différé d'un mail, sur la file `mails`, traité par les workers que
 * lance supervisor (docker/supervisor/supervisord.conf).
 *
 * - chiffré en file : certains mails portent un mot de passe en clair ;
 * - mis en file seulement après le commit de la transaction en cours : un
 *   compte annulé par un rollback ne reçoit pas de mail ;
 * - 3 tentatives espacées, puis le job part dans `failed_jobs`
 *   (`php artisan queue:failed`, `php artisan queue:retry all`).
 */
class SendMailJob implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 3;

    public $backoff = [60, 300];

    public function __construct(
        public $view,
        public array $data,
        public $subject,
        public $name,
        public $email,
        public array $files = []
    ) {
        $this->onQueue('mails');
        $this->afterCommit();
    }

    public function handle(): void
    {
        Mailer::sendNow($this->view, $this->data, $this->subject, $this->name, $this->email, $this->files);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Mail « {$this->subject} » non délivré à {$this->email} après {$this->tries} tentatives : ".$e->getMessage());
    }
}
