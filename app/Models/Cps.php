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


    public function districts()
    {
        return $this->hasMany(District::class,'cps_id');
    }
    public function municipality()
    {
        return $this->belongsTo(Municipality::class,'municipality_id');
    }
}
