<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeGarderie extends Model
{
    use HasFactory;
    protected $guarded = [];


    function sg()  {
        return $this->hasMany(TypeSousGarderie::class,'type_garderie_id');
    }
}
