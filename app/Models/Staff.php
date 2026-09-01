<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Auth;
class Staff extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    /** Centre auquel ce membre du personnel est affecté. */
    public function centre()
    {
        return $this->belongsTo(Requete::class, 'centre_id');
    }

    public function promoter()
    {
        return $this->belongsTo(Promoter::class, 'promoter_id');
    }


        public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            // Génération du code unique pour chaque utilisateur
            $model->promoter_id = Auth::user()->promoter_id ;
        });
    }

}
