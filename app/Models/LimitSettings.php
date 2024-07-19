<?php

namespace App\Models;

use App\Models\Model;

class LimitSettings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_id', 'object_type', 'debit_limits', 'use_characters',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'limit_settings';

    /**********************************************************************************************
    RELATIONS
     **********************************************************************************************/

    /**
     * Get the object.
     */
    public function object()
    {
        switch ($this->object_type) {
            case 'Prompt':
                return $this->belongsTo('App\Models\Prompt\Prompt', 'object_id');
                break;
        }
        return null;
    }

    public function getOnlyTypeAttribute()
    {
        if (count($this->object->objectLimits)) {
            $type = [];
            foreach ($this->object->objectLimits as $limit) {
                $type[] = $limit->limit_type;
            }
            $types = array_flip($type);
            if (count($types) == 1 && (key($types) == 'Currency' || key($types) == 'Prompt')) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }

    }
}
