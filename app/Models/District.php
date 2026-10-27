<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class District extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    /** Le GUPS par défaut fait toujours partie de la couverture, quel que soit le chemin d'écriture. */
    protected static function booted()
    {
        static::saved(function (District $district) {
            if ($district->cps_id !== null && ($district->wasRecentlyCreated || $district->wasChanged('cps_id'))) {
                $district->gups()->syncWithoutDetaching([$district->cps_id]);
            }
        });
    }


    /** GUPS par défaut : celui qui reçoit les nouveaux dépôts de l'arrondissement. */
    public function cps()
    {
        return $this->belongsTo(Cps::class,'cps_id');
    }

    /** Tous les GUPS qui couvrent l'arrondissement, GUPS par défaut compris. */
    public function gups()
    {
        return $this->belongsToMany(Cps::class,'cps_district','district_id','cps_id')->withTimestamps();
    }


    public function Municipality()
    {
        return $this->belongsTo(Municipality::class,'municipality_id');
    }

    /** Dossiers rattachés à cet arrondissement : empêche de purger un arrondissement encore utilisé. */
    public function districtRequetes()
    {
        return $this->hasMany(Requete::class,'district_id');
    }
}
