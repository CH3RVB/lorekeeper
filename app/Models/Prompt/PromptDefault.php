<?php

namespace App\Models\Prompt;

use App\Models\Model;
use App\Services\PromptService;

class PromptDefault extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'summary'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prompt_defaults';

    /**********************************************************************************************
    RELATIONS
     **********************************************************************************************/

    /**
     * Get the rewards attached to this prompt.
     */
    public function rewards()
    {
        return $this->hasMany('App\Models\Prompt\PromptReward', 'prompt_id')->where('prompt_type', 'Default');
    }

}