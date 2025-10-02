<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferalControl extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function transmissions()
    {
        return $this->hasMany(TransmissionReferalControl::class,'referal_control_id');
    }
}
