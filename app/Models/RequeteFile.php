<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequeteFile extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function file()
    {
        return $this->belongsTo(File::class,'file_id');
    }

    public function deliver()
    {
        return $this->belongsTo(User::class,'delivery_by');
    }
    public function requete()
    {
        return $this->belongsTo(Requete::class,'requete_id');
    }
  

}
