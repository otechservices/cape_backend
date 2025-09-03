<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
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
