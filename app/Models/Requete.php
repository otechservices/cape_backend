<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requete extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(RequeteFile::class,'requete_id')->where('level',0);
    }

    public function files2()
    {
        return $this->hasMany(RequeteFile::class,'requete_id')->where('level',1);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class,'requete_id');
    }
    
    public function reponse()
    {
        return $this->hasOne(Reponse::class,'requete_id')->orderBy("id","desc");
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class,'requete_id');
    }


    public function affectation()
    {
        return $this->hasOne(Affectation::class,'requete_id')->where('isLast',true);
    }

    public function parcours()
    {
        return $this->hasMany(Parcours::class,'requete_id');
    }

    public function lastParcours()
    {
        return $this->hasOne(Parcours::class,'requete_id')->orderBy('id','desc')->take(1);
    }

    public function NaturePromotor()
    {
        return $this->belongsTo(NaturePromotor::class,'nature_promotor_id');
    }
    public function TypeCape()
    {
        return $this->belongsTo(TypeCape::class,'type_cape_id');
    }
    public function RequeteTypeGarderies()
    {
        return $this->hasMany(RequeteTypeGarderie::class,'requete_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class,'service_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class,'district_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class,'session_id');
    }
    public function referals()
    {
        return $this->hasMany(Referal::class,'requete_id');
    }

    public function Cape()
    {
        return $this->hasOne(Cape::class,'requete_id');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class,'requete_id');
    }

    public function myAvis()
    {
        return $this->hasOne(Avis::class,'requete_id');
    }
}
