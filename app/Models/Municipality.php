<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Municipality extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];


    public function Department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    public function districts()
    {
        return $this->hasMany(District::class,'municipality_id');
    }



}
