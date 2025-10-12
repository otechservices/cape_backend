<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Reponse extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
