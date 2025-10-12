<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class TypeSousGarderie extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];


    public function TypeGarderie()
    {
        return $this->belongsTo(TypeGarderie::class,'type_garderie_id');
    }
}
