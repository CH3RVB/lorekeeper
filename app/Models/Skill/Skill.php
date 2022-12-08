<?php

namespace App\Models\Skill;

use Config;
use App\Models\Model;

class Skill extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'skill_category_id', 'parent_id', 'parent_level', 'prerequisite_id', 'has_image'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skills';
    
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:rarities|between:3,100',
        'description' => 'nullable',
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
        'description' => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the skill belongs to.
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Skill\SkillCategory', 'skill_category_id');
    }

    /**
     * Get the children of the skill
     */
    public function children()
    {
        return $this->hasMany('App\Models\Skill\Skill', 'parent_id');
    }

    /**
     * Get the parent the skill belongs to.
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Skill\Skill', 'parent_id');
    }

    /**
     * Get the prerequisite the skill belongs to.
     */
    public function prerequisite()
    {
        return $this->belongsTo('App\Models\Skill\Skill', 'prerequisite_id');
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
        return '<a href="'.$this->url.'" class="display-skill">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/skills';
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
        return url('world/skills?name='.$this->name);
    }

    /**
     * Gets the URL of the individual skill's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('world/skills/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'skills';
    }
}
