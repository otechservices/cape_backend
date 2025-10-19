<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Parcours extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];

    protected $guarded = [];



    public function requete()
    {
        return $this->belongsTo(Requete::class,'requete_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
