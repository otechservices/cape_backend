<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Requete extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    /** Dossier arrivé au bout du circuit de la plateforme. */
    public const STATUS_AUTORISE = 8;

    /** CAPE/Garderie déjà agréé avant la plateforme, chargé par import Excel. */
    public const STATUS_AGREE_IMPORTE = 9;

    /**
     * Agrément délivré hors plateforme, en attente de reconnaissance par la DFEA.
     *
     * Statut pivot des deux parcours de régularisation : le centre importé que
     * son promoteur revendique, et le nouveau dossier dont le promoteur déclare
     * détenir déjà un agrément. Dans les deux cas la DFEA tranche, et le dossier
     * n'emprunte pas le circuit d'instruction complet.
     *
     * @see \App\Models\AgrementClaim
     */
    public const STATUS_AGREMENT_A_VALIDER = 10;

    /**
     * Un dossier est agréé s'il a été autorisé via la plateforme
     * (agrément délivré) ou s'il provient de l'import des agréments existants.
     */
    public function scopeAgree($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_AGREE_IMPORTE)
                ->orWhere('status', self::STATUS_AUTORISE)
                ->orWhere('has_agreemant', 1)
                ->orWhere('is_authorized', 1)
                ->orWhereHas('Cape');
        });
    }

    /**
     * Dossiers déposés sur la plateforme et pas encore agréés.
     *
     * Les conditions sont formulées en positif plutôt qu'en négation du scope
     * `agree` : `has_agreemant` et `is_authorized` sont NULL sur la quasi-totalité
     * des lignes, or en SQL `NOT (... OR NULL)` vaut NULL et écarterait donc
     * toutes les lignes.
     *
     * Les lignes sans dénomination sont exclues : ce sont des résidus d'import,
     * pas des dossiers réels.
     */
    public function scopeNonAgree($query)
    {
        return $query
            ->whereNotIn('status', [self::STATUS_AUTORISE, self::STATUS_AGREE_IMPORTE])
            ->where(fn ($q) => $q->whereNull('has_agreemant')->orWhere('has_agreemant', '<>', 1))
            ->where(fn ($q) => $q->whereNull('is_authorized')->orWhere('is_authorized', '<>', 1))
            ->whereDoesntHave('Cape')
            ->whereNotNull('name')
            ->where('name', '<>', 'N/A');
    }

    public function getIsAgreeAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_AGREE_IMPORTE, self::STATUS_AUTORISE], true)
            || $this->has_agreemant == 1
            || $this->is_authorized == 1;
    }

    /**
     * Filtres pilotés par l'utilisateur métier. La liste affichée à l'écran et
     * le fichier exporté passent tous deux par ici : l'export correspond donc
     * toujours exactement à ce qui est filtré.
     *
     * @param  array  $filters  service_id, department_id, status, agrement
     *                          ('agree'|'non_agree'), search, date_from, date_to
     */
    public function scopeApplyFilters($query, array $filters)
    {
        $query->when($filters['agrement'] ?? null, function ($q, $agrement) {
            $agrement === 'agree' ? $q->agree() : $q->nonAgree();
        });

        $query->when($filters['service_id'] ?? null,
            fn ($q, $id) => $q->where('service_id', $id));

        $query->when($filters['status'] ?? null, function ($q, $status) {
            is_array($status) ? $q->whereIn('status', $status) : $q->where('status', $status);
        });

        $query->when($filters['department_id'] ?? null, fn ($q, $id) => $q->whereHas(
            'district.Municipality', fn ($m) => $m->where('department_id', $id)
        ));

        // Présence du rapport d'enquête sociale : permet de repérer d'un coup
        // d'œil les dossiers pour lesquels l'enquête reste à mener.
        $query->when($filters['enquete'] ?? null, function ($q, $enquete) {
            $q->where('has_cps_file', $enquete === 'avec' ? 1 : 0);
        });

        $query->when($filters['search'] ?? null, fn ($q, $term) => $q->where(
            fn ($s) => $s->where('name', 'like', "%$term%")
                ->orWhere('code', 'like', "%$term%")
                ->orWhere('name_pomoter', 'like', "%$term%")
        ));

        $query->when($filters['date_from'] ?? null,
            fn ($q, $date) => $q->whereDate('created_at', '>=', $date));

        $query->when($filters['date_to'] ?? null,
            fn ($q, $date) => $q->whereDate('created_at', '<=', $date));

        return $query;
    }

    public function files()
    {
        return $this->hasMany(RequeteFile::class,'requete_id')->where('level',0);
    }

    public function files2()
    {
        return $this->hasMany(RequeteFile::class,'requete_id')->where('level',1);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class,'requete_id');
    }
    
    public function reponse()
    {
        return $this->hasOne(Reponse::class,'requete_id')->orderBy("id","desc");
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class,'requete_id');
    }


    public function affectation()
    {
        return $this->hasOne(Affectation::class,'requete_id')->where('isLast',true);
    }

    public function parcours()
    {
        return $this->hasMany(Parcours::class,'requete_id');
    }

    public function lastParcours()
    {
        return $this->hasOne(Parcours::class,'requete_id')->orderBy('id','desc')->take(1);
    }

    public function NaturePromotor()
    {
        return $this->belongsTo(NaturePromotor::class,'nature_promotor_id');
    }
    public function TypeCape()
    {
        return $this->belongsTo(TypeCape::class,'type_cape_id');
    }
    public function RequeteTypeGarderies()
    {
        return $this->hasMany(RequeteTypeGarderie::class,'requete_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class,'service_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class,'district_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class,'session_id');
    }
    public function referals()
    {
        return $this->hasMany(Referal::class,'requete_id');
    }

    public function Cape()
    {
        return $this->hasOne(Cape::class,'requete_id');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class,'requete_id');
    }

    public function myAvis()
    {
        return $this->hasOne(Avis::class,'requete_id');
    }

     public function promoter()
    {
        return $this->belongsTo(Promoter::class,'promoter_id');
    }

    public function agrementClaims()
    {
        return $this->hasMany(AgrementClaim::class, 'requete_id');
    }

    /**
     * La demande de reconnaissance d'agrément en cours, s'il y en a une.
     * Une seule peut être vivante à la fois (voir AgrementClaim::scopeOngoing).
     */
    public function agrementClaim()
    {
        return $this->hasOne(AgrementClaim::class, 'requete_id')->ongoing();
    }

    /**
     * Dossiers dont l'agrément hors plateforme attend la décision de la DFEA.
     */
    public function scopeAgrementAValider($query)
    {
        return $query->where('status', self::STATUS_AGREMENT_A_VALIDER);
    }
}
