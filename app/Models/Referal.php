<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referal extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function controls()
    {
        return $this->hasMany(ReferalControl::class,'referal_id');
    }
    public function myControls()
    {
        return $this->hasMany(ReferalControl::class,'referal_id');
    }
    public function transmittedControls()
    {
        return $this->hasMany(ReferalControl::class,'referal_id');
    }

}
