<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cape extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function controls()
    {
        return $this->hasMany(Control::class,'cape_id');
    }
    public function myControls()
    {
        return $this->hasMany(Control::class,'cape_id');
    }
    public function transmittedControls()
    {
        return $this->hasMany(Control::class,'cape_id');
    }
    public function referals()
    {
        return $this->hasMany(Referal::class,'cape_id');
    }
    public function residents()
    {
        return $this->hasMany(Resident::class,'cape_id');
    }
    public function residentsCount()
    {
        return $this->residents()->count();
    }
    public function staffs()
    {
        return $this->hasMany(Staff::class,'cape_id');
    }
    public function staffsCount()
    {
        return $this->staffs()->count();
    }

    public function requete()
    {
        return $this->belongsTo(Requete::class,'requete_id');
    }

    public function ActivityReports()
    {
        return $this->hasMany(ActivityReport::class,'cape_id');
    }
}
