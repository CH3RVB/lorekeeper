<?php

namespace App\Models\Claymore;

use Config;
use App\Models\Model;

class WeaponStat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'weapon_id', 'stat_id', 'count'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'weapon_stats';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    public function weapon() 
    {
        return $this->belongsTo('App\Models\Claymore\Weapon');
    }

    public function stat() 
    {
        return $this->belongsTo('App\Models\Stat\Stat');
    }
    
}