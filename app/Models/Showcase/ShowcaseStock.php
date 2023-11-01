<?php

namespace App\Models\Showcase;

use App\Models\Model;

class ShowcaseStock extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'showcase_id', 'item_id', 'quantity','data', 'stock_type', 'is_visible'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'showcase_stock';

    /**********************************************************************************************
    
        RELATIONS
    **********************************************************************************************/
    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() 
    {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute()
    {
        if(!isset($this->data['disallow_transfer']) && $this->item->allow_transfer) return true;
        return false;
    }

    /**
     * Get the item being stocked.
     */
    public function item() 
    {
        return $this->belongsTo('App\Models\Item\Item');
    }

    /**
     * Get the showcase that holds this item.
     */
    public function showcase() 
    {
        return $this->belongsTo('App\Models\Showcase\Showcase', 'showcase_id');
    }

     /**
     * Scopes active stock
     */
    public function scopeActive($query)
    {
        return $query->where('is_visible', 1);
    }

}