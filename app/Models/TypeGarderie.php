<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class TypeGarderie extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];


    function sg()  {
        return $this->hasMany(TypeSousGarderie::class,'type_garderie_id');
    }
}
