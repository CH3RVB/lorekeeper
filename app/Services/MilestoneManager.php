<?php

namespace App\Services;

use App\Models\Milestone;
use App\Models\User\User;
use App\Models\User\UserMilestone;
use DB;

class MilestoneManager extends Service {
    /**********************************************************************************************
         Milestone completion
     **********************************************************************************************/

    /**
     * Attempts to complete the specified milestone.
     *
     * @param Milestone $milestone
     * @param User      $user
     *
     * @return bool
     */
    public function completeMilestone($milestone, $user) {
        DB::beginTransaction();

        try {
            if (!$milestone->canClaim($user)) {
                throw new \Exception('You cannot complete this milestone or have already completed it.');
            }

            // Credit rewards
            $milestoneData = [
                'data' => 'Received rewards from '.$milestone->displayName.' milestone',
            ];

            if (count(objectRewards($milestone, 'objectRewards', 'User'))) {
                $rewService = new RewardManager;
                // Distribute user rewards
                if (!$rewService->grantRewards($milestone, null, $user, ['log_type' => 'Milestone Reward', 'log_data' => $milestoneData, 'reward_key' => 'objectRewards', 'flash_rewards' => 1])) {
                    foreach ($rewService->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                    throw new \Exception('Failed to distribute rewards to user.');
                }
            }

            $record = UserMilestone::create(['user_id' => $user->id, 'milestone_id' => $milestone->id]);
            if (!$record) {
                throw new \Exception('Failed to record user milestone.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
