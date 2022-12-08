<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Carbon\Carbon;
use App\Models\Skill\SkillCategory;
use App\Models\Skill\Skill;
use App\Models\Character\CharacterSkill;
use App\Models\Character\CharacterLog;

class SkillManager extends Service
{
/*
    |--------------------------------------------------------------------------
    | Skill Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned skills.
    |
    */

    /**
     * Credits an skill to a character.
     *
     * @param  \App\Models\Character\Character  $sender
     * @param  \App\Models\Character\Character  $recipient
     * @param  string                                                 $type 
     * @param  array                                                  $data
     * @param  \App\Models\Skill\Skill                                  $skill
     * @param  int                                                    $quantity
     * @return bool
     */
    public function creditSkill($sender, $recipient, $skill, $quantity, $type)
    {
        DB::beginTransaction();

        try {

            $recipient_stack = CharacterSkill::where([
                ['character_id', '=', $recipient->id],
                ['skill_id', '=', $skill->id]
            ])->first();

            if(!$recipient_stack) {
                $data = 'Received ' . $quantity . ' points for ' . $skill->name . ' skill. Previous: 0';

                $recipient_stack = CharacterSkill::create(['character_id' => $recipient->id, 'skill_id' => $skill->id, 'level' => $quantity]);
            }
            else {
                $data = 'Received ' . $quantity . ' points for ' . $skill->name . ' skill. Previous: ' . $recipient_stack->level;

                $recipient_stack->level += $quantity;
                $recipient_stack->save();
            }

            if($type && !$this->createLog($recipient->id, $sender->id, $type, $data)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function createLog($recipientId, $senderId, $type, $data)
    {
        
        return DB::table('character_log')->insert(
            [
                'character_id' => $recipientId,
                'sender_id' => $senderId,
                'log' => 'Skill Awarded (' . $type . ')',
                'log_type' => 'Skill Awarded',
                'data' => $data, // this should be just a string
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
    }
}
