<?php

namespace App\Models\Claymore;

use Config;
use App\Models\Model;

class GearEnchantment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enchantment_id', 'quantity',  'gear_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gear_enchantments';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    public function gear() 
    {
        return $this->belongsTo('App\Models\Claymore\Gear');
    }

    public function enchantment() 
    {
        return $this->belongsTo('App\Models\Claymore\Enchantment');
    }
    
}