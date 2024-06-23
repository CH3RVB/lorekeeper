<?php

namespace App\Models;

use App\Models\Model;

class ObjectLimit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_id', 'object_type', 'limit_id', 'limit_type', 'quantity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'object_limits';

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
        }
        return null;
    }

    /**
     * Get the limit.
     */
    public function limit()
    {
        switch ($this->limit_type) {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'limit_id');
            case 'Currency':
                return $this->belongsTo('App\Models\Currency\Currency', 'limit_id');
        }
        return null;
    }

}
