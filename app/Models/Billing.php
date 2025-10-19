<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Billing extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    public function responses()
    {
        return $this->hasMany(BillingResponse::class,'billing_id');
    }

    public function type()
    {
        return $this->belongsTo(TypeBilling::class,'type_billing_id');
    }

}
