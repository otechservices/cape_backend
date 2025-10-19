<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class File extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    public function TypeFile()
    {
        return $this->belongsTo(TypeFile::class,'type_document_id');
    }
    public function type()
    {
        return $this->belongsTo(service::class,'service_id');
    }
}
