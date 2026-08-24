<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use App\Utilities\Core;


class Promoter extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];

    protected $guarded = [];

       /**
     * Fonction boot pour générer un code unique avant la création
     */
    public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            // Génération du code unique pour chaque utilisateur
            $model->code = (string) Core::generateIncrementUniqueCode('promoters', 4, 'code', 'PM');
        });
    }


       public function user()
    {

        return $this->hasOne(User::class, 'promoter_id');
    }

    /**
     * Un promoteur peut détenir plusieurs centres : chaque dossier déposé ou
     * revendiqué pointe sur lui.
     */
    public function requetes()
    {
        return $this->hasMany(Requete::class, 'promoter_id');
    }

    public function agrementClaims()
    {
        return $this->hasMany(AgrementClaim::class, 'promoter_id');
    }



}
