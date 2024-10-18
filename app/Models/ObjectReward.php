<?php

namespace App\Models;

use App\Models\Model;

class ObjectReward extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_id', 'object_type', 'rewardable_id', 'rewardable_type', 'quantity','recipient_type'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'object_rewards';

    /**********************************************************************************************
    RELATIONS
     **********************************************************************************************/

    /**
     * Get the object.
     */
    public function object()
    {
        return $this->morphTo();
    }

    /**
     * Get the reward attached to the prompt reward.
     */
    public function reward()
    {
        return $this->morphTo();
    }

     /**
     * Get the reward type so we don't have to do the no-no of model names in forms
     */
    public function rewardType()
    {
        return class_basename($this->rewardable_type);
    }
}
