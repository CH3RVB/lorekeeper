<?php

namespace App\Models\Showcase;

use Config;
use App\Models\Model;

class Showcase extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'user_id', 'sort', 'has_image', 'description', 'parsed_description', 'is_active'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'showcases';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|between:3,100',
        'description' => 'nullable',
        'image' => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
        'description' => 'nullable',
        'image' => 'mimes:png',
    ];

    /**********************************************************************************************
    
        RELATIONS
    **********************************************************************************************/

    /**
     * Get the showcase stock.
     */
    public function stock()
    {
        return $this->hasMany('App\Models\Showcase\ShowcaseStock');
    }

    /**
     * Get the user who owns the character.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }
    /**
     * Get the showcase stock as items for display purposes.
     */
    public function displayStock()
    {
        return $this->belongsToMany('App\Models\Item\Item', 'showcase_stock')
            ->where('stock_type', 'Item')
            ->withPivot('item_id', 'quantity', 'id', 'is_visible')
            ->wherePivot('quantity', '>', 0)
            ->wherePivot('is_visible', 1);
    }

    /**
     * Scope a query to show only visible features.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null)
    {
        if ($user && $user->hasPower('edit_inventories')) {
            return $query;
        }

        return $query->where('is_active', 1);
    }

    /**********************************************************************************************
    
        ACCESSORS
    **********************************************************************************************/

    /**
     * Displays the showcase's name, linked to its purchase page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return (!$this->is_active ? '<i class="fas fa-eye-slash mr-1"></i>' : '') . '<a href="' . $this->url . '" class="display-showcase">' . $this->name . '</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/showcases';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getShowcaseImageFileNameAttribute()
    {
        return $this->id . '-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getShowcaseImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getShowcaseImageUrlAttribute()
    {
        if (!$this->has_image) {
            return null;
        }
        return asset($this->imageDirectory . '/' . $this->showcaseImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('/' . __('showcase.showcases') . '/' . __('showcase.showcase') . '/' . $this->id);
    }

    /**
     * Gets the showcase's log type for log creation.
     *
     * @return string
     */
    public function getLogTypeAttribute()
    {
        return 'Showcase';
    }
}
