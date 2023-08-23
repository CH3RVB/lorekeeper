<?php

namespace App\Models\Claymore;

use Config;
use App\Models\Model;

class WeaponEnchantment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enchantment_id', 'quantity',  'weapon_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'weapon_enchantments';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    public function weapon() 
    {
        return $this->belongsTo('App\Models\Claymore\Weapon');
    }

    public function enchantment() 
    {
        return $this->belongsTo('App\Models\Claymore\Enchantment');
    }
    
}