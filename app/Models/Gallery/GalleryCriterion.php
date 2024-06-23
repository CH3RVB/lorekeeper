<?php

namespace App\Models\Gallery;

use Config;
use App\Models\Model;

class GalleryCriterion extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gallery_id', 'criterion_id', 'min_requirements'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gallery_criteria';
    
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'criterion_id' => 'required',
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'criterion_id' => 'required',
    ];

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the gallery attached to this criterion
     */
    public function gallery() 
    {
       return $this->belongsTo('App\Models\Gallery\Gallery', 'gallery_id');
    }
    
    /**
     * Get the criterion attached to this prompt
     */
    public function criterion() 
    {
       return $this->belongsTo('App\Models\Criteria\Criterion', 'criterion_id');
    }
    
    
   /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getMinRequirementsAttribute()
    {
        return json_decode($this->attributes['min_requirements'], true);
    }
}
