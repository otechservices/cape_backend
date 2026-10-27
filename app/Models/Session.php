<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Session extends Model
{
    use HasFactory,Filterable;
private static $whiteListFilter = ['*'];
    protected $guarded = [];

    /** Modes de clôture, cf. migration add_closure_fields_to_sessions_table. */
    public const CLOSURE_NORMAL = 'normal';
    public const CLOSURE_FORCED_CONTINUE = 'forced_continue';
    public const CLOSURE_FORCED_CANCEL = 'forced_cancel';

    /**
     * Sessions dont les délibérations comptent : toutes sauf les annulées.
     *
     * À appliquer partout où l'on cherche « la dernière session close » pour
     * enchaîner sur les décisions, sinon une session annulée ressortirait.
     */
    public function scopeNotCancelled($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('closure_mode')->orWhere('closure_mode', '!=', self::CLOSURE_FORCED_CANCEL);
        });
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }


    public function sessionMembers()
    {
        return $this->hasMany(SessionMember::class,'session_id');
    }


    public function requetes()
    {
        return $this->hasMany(Requete::class,'session_id');
    }

    public function requetesOk()
    {
        return $this->hasMany(Requete::class,'session_id')->where('is_authorized',true);
    }
    public function requetesNok()
    {
        return $this->hasMany(Requete::class,'session_id')->where('is_authorized',false);
    }
}
