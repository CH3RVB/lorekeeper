<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Limit\Limit;
use App\Models\Submission\Submission;
use App\Models\Submission\SubmissionCharacter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LimitManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Limit Manager
    |--------------------------------------------------------------------------
    |
    | Handles the checking of limits on objects
    |
    */

    /**********************************************************************************************

        LIMITS

    **********************************************************************************************/

    /**
     * checks all limits on an object.
     *
     * @param mixed      $object
     * @param mixed      $is_unlock
     * @param mixed|null $data
     * @param mixed|null $logType
     * @param mixed|null $logData
     */
    public function checkLimits($object, $is_unlock = false, $data = null, $logType = null, $logData = null) {
        try {
            $user = Auth::user();

            $limits = hasLimits($object) ? getLimits($object) : [];
            if (!count($limits)) {
                return true;
            }

            if ($limits->first()->is_unlocked) {
                if (!$user) {
                    throw new \Exception('You must be logged in to complete this action.');
                }

                if (hasUnlockedLimits($user, $object)) {
                    return true;
                }
            }

            // if the limit is not unlocked, check if it is auto unlocked
            if (!$is_unlock && !$limits->first()->is_auto_unlocked) {
                throw new \Exception(($limits->first()->object->displayName ?? $limits->first()->object->name).' requires manual unlocking!');
            }

            // prepare the array
            // we will ignore if it there aren't any debit limits

            $debitCharacterAssets = createAssetsArray(true);
            $debitUserAssets = createAssetsArray();
            foreach ($limits as $limit) {
                if ($limit->recipient_type == 'Character') {
                    $isCharacter = true;
                    if (!$data || !isset($data['character_id']) || !count($data['character_id'])) {
                        throw new \Exception('A character must be selected or attached to complete this action.');
                    }
                    $recipient = Character::find($data['character_id']);
                } else {
                    $isCharacter = false;
                    $recipient = $user;
                }

                switch ($limit->limit_type) {
                    case 'prompt':
                        if (!$limit->quantity) {
                            continue 2;
                        }

                        // check at least quantity of prompts has been approved

                        if ($isCharacter) {
                            $recipient_prompts = Submission::whereIn('id', SubmissionCharacter::where('character_id', $recipient->id)->toArray())->get();
                        } else {
                            $recipient_prompts = Submission::where('user_id', $user->id);
                        }

                        if ($recipient_prompts->where('prompt_id', $limit->limit_id)->where('status', 'Approved')->count() < $limit->quantity) {
                            throw new \Exception('You have not completed the prompt ' . $limit->limit->displayName . ' enough times to complete this action.');
                        }
                        break;
                    case 'item':

                        $stacks = $recipient->items()->where('item_id', $limit->limit_id);
                        if (!$stacks->sum('count') >= $limit->quantity) {
                            throw new \Exception('You do not have enough of the item '.$limit->object->name.' to complete this action.');
                        }

                        if (!$limit->quantity) {
                            continue 2;
                        }
                        if ($limit->debit) {
                            $stacks = $stacks->orderBy('count', 'asc')->get(); // asc because pop() removes from the end

                            if ($limit->debit) {
                                $addasset = ($isCharacter ? $debitCharacterAssets : $debitUserAssets);
                                addAsset($addasset, $limit->limit, -$limit->quantity);
                            }
                        }
                        break;
                    case 'currency':

                        $currencies = $isCharacter ? DB::table('character_currencies')->where('character_id', $recipient->id) : DB::table('user_currencies')->where('user_id', $user->id);

                        if (!$limit->quantity) {
                            continue 2;
                        }

                        if ($currencies->where('currency_id', $limit->limit_id)->value('quantity') < $limit->quantity) {
                            throw new \Exception('You do not have enough '.$limit->limit->displayName.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $addasset = ($isCharacter ? $debitCharacterAssets : $debitUserAssets);
                            addAsset($addasset, $limit->limit, -$limit->quantity);
                        }
                        break;
                    case 'dynamic':
                        if (!$this->checkDynamicLimit($limit, $user)) {
                            throw new \Exception('You do not meet the requirements to complete this action.');
                        }
                        break;
                }
            }
            if (countAssets($debitUserAssets) > 0) {
                if (!takeUserAssets($debitUserAssets, $user, null, $data['logType'] ?? 'Limit Requirements', [
                    'data' => $data['logData'] ?? 'Used in '.$limit->object->displayName.' limit requirements.',
                ])) {
                    throw new \Exception('Failed to debit user limits.');
                }
            }
            if (countAssets($debitCharacterAssets) > 0) {
                if (!fillCharacterAssets($debitCharacterAssets, $recipient, null, $data['logType'] ?? 'Limit Requirements', [
                    'data' => $data['logData'] ?? 'Used in '.$limit->object->displayName.' limit requirements.',
                ])) {
                    throw new \Exception('Failed to debit character limits.');
                }
            }

            if ($limits->first()->is_unlocked && $limits->first()->is_auto_unlocked || $is_unlock) {
                $user->unlockedLimits()->create([
                    'object_model' => get_class($object),
                    'object_id'    => $object->id,
                    'data'         => [
                        'type'      => $data['type'] ?? null,
                        'character' => $data['character_id'] ?? null,
                    ],
                ]);
            } elseif (!$is_unlock && $limits->first()->is_unlocked && !$limits->first()->is_auto_unlocked) {
                throw new \Exception(($limits->first()->object->displayName ?? $limits->first()->object->name).' requires manual unlocking!');
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * checks a dynamic limit.
     *
     * @param mixed $limit
     * @param mixed $user
     */
    private function checkDynamicLimit($limit, $user) {
        try {
            return $limit->limit->evaluate();
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }
}
