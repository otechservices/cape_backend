<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Control extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];


    public function cape()
    {
        return $this->belongsTo(Cape::class,'cape_id');
    }
    public function TypeControl()
    {
        return $this->belongsTo(TypeControl::class,'type_control_id');
    }


    public function transmissions()
    {
        return $this->hasMany(TransmissionControl::class,'control_id');
    }
}
