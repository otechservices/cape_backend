<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Department extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];


    public function Municipalities()
    {
        return $this->hasMany(Municipality::class,'department_id');
    }

}
