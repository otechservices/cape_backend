<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Resident extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];



    function centre() {
        return $this->belongsTo(Requete::class,'centre_id');
    }

        function promoter() {
        return $this->belongsTo(Promoter::class,'promoter_id');
    }
 
}
