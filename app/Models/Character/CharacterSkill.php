<?php

namespace App\Models\Character;

use Config;
use DB;
use App\Models\Model;

class CharacterSkill extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'skill_id', 'level'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_skills';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character this profile belongs to.
     */
    public function character()
    {
        return $this->belongsTo('App\Models\Character\Character', 'character_id');
    }

    /**
     * Get the skill.
     */
    public function skill()
    {
        return $this->belongsTo('App\Models\Skill\Skill', 'skill_id');
    }
}
