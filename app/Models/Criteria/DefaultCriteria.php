<?php

namespace App\Models\Criteria;

use Config;
use App\Models\Model;

class DefaultCriteria extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'criteriondefault_id', 'criterion_id', 'min_requirements','criterion_currency_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'default_criteria';

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

    /**********************************************************************************************
    
        RELATIONS
    **********************************************************************************************/

    /**
     * Get the prompt attached to this criterion
     */
    public function default() 
    {
       return $this->belongsTo('App\Models\Criteria\CriterionDefault', 'criteriondefault_id');
    }

    /**
     * Get the criterion attached to this prompt
     */
    public function criterion() 
    {
       return $this->belongsTo('App\Models\Criteria\Criterion', 'criterion_id');
    }
}