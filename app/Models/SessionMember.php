<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionMember extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function member()
    {
        return $this->belongsTo(Member::class,'member_id');
    }
    public function session()
    {
        return $this->belongsTo(Session::class,'session_id');
    }
    public function avis()
    {
        return $this->hasMany(Avis::class,'session_member_id');
    }


    public function user()
    {
        return $this->hasOne(User::class,'session_member_id');
    }
}
