<?php

namespace App\Models\Claymore;

use Config;
use App\Models\Model;

class GearStat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gear_id', 'stat_id', 'count'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gear_stats';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    public function gear() 
    {
        return $this->belongsTo('App\Models\Claymore\Gear');
    }

    public function stat() 
    {
        return $this->belongsTo('App\Models\Stat\Stat');
    }
    
}