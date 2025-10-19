<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class ActivityReport extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    public function centre()
    {
        return $this->belongsTo(Requete::class,'centre_id');
    }


    public function promoter()
    {
        return $this->belongsTo(Promoter::class,'promoter_id');
    }
   
   

    public function responses()
    {
        return $this->hasMany(ActivityReportResponse::class,'activity_report_id');
    }

}
