<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function sm()
    {
        return $this->belongsTo(SessionMember::class,'session_member_id');
    }

    public function requeteFile()
    {
        return $this->belongsTo(RequeteFile::class,'requete_file_id');
    }
}
