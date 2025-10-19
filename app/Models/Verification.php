<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Verification extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'email',
        'phone',
        'code',
        'sms',
        'whatsapp',
        'expired_at',
    ];
}
