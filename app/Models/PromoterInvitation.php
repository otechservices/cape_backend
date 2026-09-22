<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Lien de création de compte promoteur, rattachant un centre au compte créé.
 */
class PromoterInvitation extends Model
{
    /** Durée de validité du lien envoyé au promoteur. */
    public const VALIDITE_JOURS = 7;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function requete()
    {
        return $this->belongsTo(Requete::class, 'requete_id');
    }

    public function promoter()
    {
        return $this->belongsTo(Promoter::class, 'promoter_id');
    }

    /**
     * Crée l'invitation et renvoie le jeton en clair, qui n'existera nulle part
     * ailleurs que dans le lien envoyé.
     *
     * @return array{0: PromoterInvitation, 1: string}
     */
    public static function issue(array $attributes): array
    {
        $token = Str::random(64);

        $invitation = self::create(array_merge($attributes, [
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(self::VALIDITE_JOURS),
        ]));

        return [$invitation, $token];
    }

    public static function findByToken(string $token): ?self
    {
        return self::where('token_hash', hash('sha256', $token))->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /** Adresse de création du compte, côté application Angular. */
    public static function url(string $token): string
    {
        return env('APP_FRONT_URL').'/public/auth/invitation/'.$token;
    }
}
