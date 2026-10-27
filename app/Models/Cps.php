<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Cps extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];


    /** Arrondissements dont ce GUPS est le GUPS par défaut (reçoit les nouveaux dépôts). */
    public function districts()
    {
        return $this->hasMany(District::class,'cps_id');
    }

    /** Arrondissements couverts, y compris ceux partagés avec d'autres GUPS. */
    public function couverture()
    {
        return $this->belongsToMany(District::class,'cps_district','cps_id','district_id')->withTimestamps();
    }
    public function municipality()
    {
        return $this->belongsTo(Municipality::class,'municipality_id');
    }
}
