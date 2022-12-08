<?php

namespace App\Models\Pet;

use Config;
use DB;
use App\Models\Model;
use App\Models\Pet\PetCategory;

class Pet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_category_id', 'name', 'has_image', 'description', 'parsed_description', 'allow_transfer', 'pet_name'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pets';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'pet_category_id' => 'nullable',
        'name' => 'required|unique:pets|between:3,25',
        'description' => 'nullable',
        'image' => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'pet_category_id' => 'nullable',
        'name' => 'required|between:3,25',
        'description' => 'nullable',
        'image' => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the pet belongs to.
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Pet\PetCategory', 'pet_category_id');
    }

    public function variants()
    {
        return $this->hasMany('App\Models\Pet\PetVariant', 'pet_id');
    }

    /**
     * Get the drop data associated with this species.
     */
    public function dropData()
    {
        return $this->hasOne('App\Models\Pet\PetDropData');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort pets in alphabetical order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $reverse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false)
    {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort pets in category order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query)
    {
        $ids = PetCategory::orderBy('sort', 'DESC')->pluck('id')->toArray();
        return count($ids) ? $query->orderByRaw(DB::raw('FIELD(pet_category_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort pets by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->url.'" class="display-item">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/pets';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute()
    {
        return $this->id . '-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!$this->has_image) return null;
        return asset($this->imageDirectory . '/' . $this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('world/pets?name='.$this->name);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'pets';
    }

    public function VariantImage($id = null)
    {
        if(!$id) return $this->imageUrl;
        else return $this->variants()->where('id', $id)->first()->imageUrl;
    }

    public function VariantName($id = null)
    {
        if(!$id || !$this->variants() ) return '';
        else return $this->variants()->where('id', $id)->first()->variant_name;
    }

    /**
     * Gets whether or not the pet has drops.
     *
     * @return string
     */
    public function getHasDropsAttribute()
    {
        if($this->dropData) return 1;
        else return 0;
    }
}
