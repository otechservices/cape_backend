<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cps extends Model
{
    use HasFactory;
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
