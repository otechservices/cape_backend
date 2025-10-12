<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Session extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];


    public function sessionMembers()
    {
        return $this->hasMany(SessionMember::class,'session_id');
    }


    public function requetes()
    {
        return $this->hasMany(Requete::class,'session_id');
    }

    public function requetesOk()
    {
        return $this->hasMany(Requete::class,'session_id')->where('is_authorized',true);
    }
    public function requetesNok()
    {
        return $this->hasMany(Requete::class,'session_id')->where('is_authorized',false);
    }
}
