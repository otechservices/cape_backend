<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Utilities\Core;


class Promoter extends Model
{
    use HasFactory;

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


}
