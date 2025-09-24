<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function cps()
    {
        return $this->belongsTo(Cps::class,'cps_id');
    }


    public function Municipality()
    {
        return $this->belongsTo(Municipality::class,'municipality_id');
    }
}
