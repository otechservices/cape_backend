<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class ReferalControl extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];

    public function transmissions()
    {
        return $this->hasMany(TransmissionReferalControl::class,'referal_control_id');
    }
}
