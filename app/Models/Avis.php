<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Avis extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
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
