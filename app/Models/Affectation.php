<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Affectation extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    /** Agent qui détient le dossier à cette étape. */
    public function detenteur()
    {
        return $this->belongsTo(User::class, 'user_down');
    }

    /** Agent qui le lui a transmis. */
    public function emetteur()
    {
        return $this->belongsTo(User::class, 'user_up');
    }
}
