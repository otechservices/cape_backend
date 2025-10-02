<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sanction extends Model
{
    use HasFactory;
    protected $guarded = [];


    
    public function cape()
    {
        return $this->belongsTo(Cape::class,'cape_id');
    }
    public function TypeSanction()
    {
        return $this->belongsTo(TypeSanction::class,'type_sanction_id');
    }
}
