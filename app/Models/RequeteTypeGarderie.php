<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class RequeteTypeGarderie extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];


    public function TypeGarderie()
    {
        return $this->belongsTo(TypeGarderie::class,'type_garderie_id');
    }
}
