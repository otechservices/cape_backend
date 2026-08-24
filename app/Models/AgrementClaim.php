<?php

namespace App\Models;

use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Demande de reconnaissance d'un agrément délivré hors plateforme.
 *
 * @see database/migrations/2026_08_24_100000_create_agrement_claims_table.php
 */
class AgrementClaim extends Model
{
    use HasFactory, Filterable;

    private static $whiteListFilter = ['*'];

    protected $guarded = [];

    protected $casts = [
        'otp_expired_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    /** Le code OTP est parti, la saisie du promoteur est attendue. */
    public const STATUS_OTP_PENDING = 0;

    /** Identité confirmée : le promoteur complète son dossier. */
    public const STATUS_VERIFIED = 1;

    /** Dossier complet transmis à la DFEA. */
    public const STATUS_SUBMITTED = 2;

    /** Agrément reconnu : le dossier est passé au statut autorisé. */
    public const STATUS_VALIDATED = 3;

    /** Agrément refusé par la DFEA. */
    public const STATUS_REJECTED = 4;

    /** Centre déjà agréé chargé par l'import Excel, revendiqué par son promoteur. */
    public const ORIGIN_IMPORT = 'import';

    /** Agrément déclaré par le promoteur au moment de l'inscription du centre. */
    public const ORIGIN_DECLARATION = 'declaration';

    /**
     * Demandes encore en vie : elles bloquent toute nouvelle revendication du
     * même centre. Une demande rejetée n'en fait pas partie, afin qu'un
     * promoteur puisse redéposer après correction.
     */
    public function scopeOngoing($query)
    {
        return $query->whereIn('status', [
            self::STATUS_OTP_PENDING,
            self::STATUS_VERIFIED,
            self::STATUS_SUBMITTED,
        ]);
    }

    public function requete()
    {
        return $this->belongsTo(Requete::class, 'requete_id');
    }

    public function promoter()
    {
        return $this->belongsTo(Promoter::class, 'promoter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
