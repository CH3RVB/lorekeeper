<?php

namespace App\Models\Prompt;

use Config;
use App\Models\Model;

class PromptSkill extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prompt_id', 'skill_id', 'quantity'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prompt_skills';
    
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'skill_id' => 'required',
        'quantity' => 'required|integer|min:1',
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'skill_id' => 'required',
        'quantity' => 'required|integer|min:1',
    ];

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the skill attached to the prompt skill.
     */
    public function skill() 
    {
        return $this->belongsTo('App\Models\Skill\Skill', 'skill_id');
    }
}
