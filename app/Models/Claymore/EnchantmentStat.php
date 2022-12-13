<?php

namespace App\Models\Claymore;

use Config;
use App\Models\Model;

class EnchantmentStat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enchantment_id', 'stat_id', 'count'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'enchantment_stats';

    /**********************************************************************************************
    
        RELATIONS
    **********************************************************************************************/

    public function enchantment() 
    {
        return $this->belongsTo('App\Models\Claymore\Enchantment');
    }

    public function stat() 
    {
        return $this->belongsTo('App\Models\Stat\Stat');
    }

}