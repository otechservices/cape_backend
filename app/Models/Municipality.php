<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;
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
