<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class ActivityReportResponse extends Model
{
    use HasFactory,Filterable;
    protected $guarded = [];

}
