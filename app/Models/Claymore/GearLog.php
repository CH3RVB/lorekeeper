<?php

namespace App\Models\Claymore;

use Config;
use App\Models\Model;

class GearLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'recipient_id', 
        'log', 'log_type', 'data',
        'gear_id', 'quantity', 'stack_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_gears_log';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the logged action.
     */
    public function sender() 
    {
        return $this->belongsTo('App\Models\User\User', 'sender_id');
    }

    /**
     * Get the user who received the logged action.
     */
    public function recipient() 
    {
        return $this->belongsTo('App\Models\User\User', 'recipient_id');
    }

    /**
     * Get the gear that is the target of the action.
     */
    public function gear() 
    {
        return $this->belongsTo('App\Models\Claymore\Gear');
    }
}
