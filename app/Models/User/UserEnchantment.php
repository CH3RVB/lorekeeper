<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEnchantment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enchantment_id', 'user_id'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    protected $dates = ['attached_at'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_enchantments';

    /**********************************************************************************************
    
        RELATIONS
    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the enchantment associated with this enchantment stack.
     */
    public function enchantment() 
    {
        return $this->belongsTo('App\Models\Claymore\Enchantment');
    }

    public function gear()
    {
        return $this->belongsTo('App\Models\User\UserGear', 'gear_stack_id');
    }

    public function weapon()
    {
        return $this->belongsTo('App\Models\User\UserWeapon', 'weapon_stack_id');
    }

    /**********************************************************************************************
    
        ACCESSORS
    **********************************************************************************************/


    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'user_enchantments';
    }

    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute()
    {
        if(!isset($this->data['disallow_transfer']) && $this->enchantment->allow_transfer) return true;
        return false;
    }

}