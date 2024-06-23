<?php

namespace App\Models\Prompt;

use Config;
use App\Models\Model;

class PromptCriterion extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prompt_id', 'criterion_id', 'min_requirements'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prompt_criteria';
    
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
    public function prompt() 
    {
       return $this->belongsTo('App\Models\Prompt\Prompt', 'prompt_id');
    }
    
    /**
     * Get the criterion attached to this prompt
     */
    public function criterion() 
    {
       return $this->belongsTo('App\Models\Criteria\Criterion', 'criterion_id');
    }
}
