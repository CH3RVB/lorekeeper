<?php
namespace App\Services;

use App\Models\ObjectReward;
use DB;

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
     * Edit reward.
     *
     * @param mixed $object
     * @param mixed $data
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editRewards($object, $data)
    {
        DB::beginTransaction();
        try {
            if (! isset($data['reward_key'])) {
                throw new \Exception('You must set a reward key.');
            }
            if (! isset($data['recipient_type'])) {
                throw new \Exception('You must select a recipient.');
            }

            // We're going to remove all rewards and reattach them with the updated data

            foreach (objectRewards($object, $data['reward_key'], $data['recipient_type']) as $reward) {
                $reward->delete();
            }

            if (isset($data['rewardable_type'])) {
                foreach ($data['rewardable_type'] as $key => $type) {
                    $model = strtolower($type);

                    ObjectReward::create([
                        'object_id'       => $object->id,
                        'object_type'     => get_class($object),
                        'rewardable_type' => getAssetModelString($model),
                        'rewardable_id'   => $data['rewardable_id'][$key] ?? null,
                        'quantity'        => $data['reward_quantity'][$key],
                        'recipient_type'  => $data['recipient_type'],
                        'reward_key'      => $data['reward_key'],
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
     * Grant rewards.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $object
     * @param mixed                 $recipient
     * @param mixed                 $isCharacter
     *
     * @return mixed
     */
    public function grantRewards($object, $user, $recipient, $data, $isCharacter = false)
    {
        DB::beginTransaction();

        try {
            if (! $object) {
                throw new \Exception('Invalid object.');
            }

            if (! $recipient) {
                throw new \Exception('Invalid recipient.');
            }

            if ($isCharacter) {
                $recipient_type = 'Character';
            } else {
                $recipient_type = 'User';
            }

            $rewards = createAssetsArray();

            foreach (objectRewards($object, $data['reward_key'], $recipient_type) as $reward) {
                addAsset($rewards, $reward->reward, $reward->quantity);
            }

            if ($isCharacter) {
                // Distribute character rewards
                if (! ($rewards = fillCharacterAssets($rewards, null, $recipient, $data['log_type'], $data['log_data'], $user))) {
                    throw new \Exception('Failed to distribute rewards to character.');
                }
            } else {
                // Distribute user rewards
                if (! ($rewards = fillUserAssets($rewards, null, $recipient, $data['log_type'], $data['log_data']))) {
                    throw new \Exception('Failed to distribute rewards to user.');
                }
            }

            if (isset($data['flash_rewards']) && $data['flash_rewards'] == 1) {
                flash((isset($data['reward_prefix']) ? $data['reward_prefix'] : 'You have received: ') . createRewardsString($rewards))->success();
            }

            flash(($isCharacter ? 'Character' : 'User') . ' rewards granted successfully.')->success();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
