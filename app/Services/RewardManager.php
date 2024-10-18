<?php

namespace App\Services;

use App\Models\ObjectReward;
use DB;
use Illuminate\Http\Request;

class RewardManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Reward Maker Service
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of rewards
    |
     */

    /**
     * Edit reward
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editRewards($object, $data)
    {

        DB::beginTransaction();
        try {

            // We're going to remove all rewards and reattach them with the updated data
            $Etype = $data['recipient_type'];

            if ($Etype == 'User') {
                $object->objectRewards()->delete();
            } else {
                $object->objectCharacterRewards()->delete();
            }

            if (isset($data['rewardable_type'])) {
                foreach ($data['rewardable_type'] as $key => $type) {

                    $model = strtolower($type);

                    ObjectReward::create([
                        'object_id' => $object->id,
                        'object_type' => get_class($object),
                        'rewardable_type' => getAssetModelString($model),
                        'rewardable_id' => $data['rewardable_id'][$key] ?? null,
                        'quantity' => $data['reward_quantity'][$key],
                        'recipient_type' => $Etype,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Grant rewards
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function grantRewards($object, $user, $recipient, $logtype, $logdata, $isCharacter = false)
    {
        DB::beginTransaction();

        try {
            if (!$object) {
                throw new \Exception("Invalid object.");
            }

            if (!$recipient) {
                throw new \Exception("Invalid recipient.");
            }

            $rewards = createAssetsArray();

            if ($isCharacter) {
                foreach ($object->objectCharacterRewards as $reward) {
                    addAsset($rewards, $reward->reward, $reward->quantity);
                }

                // Distribute character rewards
                if (!($rewards = fillCharacterAssets($rewards, null, $recipient, $logtype, $logdata, $user))) {
                    throw new \Exception('Failed to distribute rewards to character.');
                }
            } else {
                foreach ($object->objectRewards as $reward) {
                    addAsset($rewards, $reward->reward, $reward->quantity);
                }

                // Distribute user rewards
                if (!($rewards = fillUserAssets($rewards, null, $recipient, $logtype, $logdata))) {
                    throw new \Exception('Failed to distribute rewards to user.');
                }
            }

            flash(($isCharacter ? 'Character' : 'User') . ' rewards granted successfully.')->success();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
