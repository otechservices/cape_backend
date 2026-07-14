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


    public function cps()
    {
        return $this->belongsTo(Cps::class,'cps_id');
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
