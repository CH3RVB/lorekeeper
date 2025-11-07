<?php
namespace App\Services;

use App\Models\Character\Character;
use App\Models\Currency\CurrencyLog;
use App\Models\Item\ItemCategory;
use App\Models\Milestone;
use App\Models\User\User;
use App\Models\User\UserCharacterLog;
use App\Models\User\UserItem;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserCurrency;

class MilestoneService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Milestone Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of milestones.
    |
    */

    /**********************************************************************************************

        MilestoneS
    **********************************************************************************************/

    /**
     * Creates a new milestone.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Milestone
     */
    public function createMilestone($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image             = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $milestone = Milestone::create($data);

            if (! $this->logAdminAction($user, 'Created Milestone', 'Created #' . $milestone->id)) {
                throw new \Exception('Failed to log admin action.');
            }
            if ($image) {
                $this->handleImage($image, $milestone->imagePath, $milestone->imageFileName);
            }

            return $this->commitReturn($milestone);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an milestone.
     *
     * @param  \App\Models\Milestone  $milestone
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Milestone
     */
    public function updateMilestone($milestone, $data, $user)
    {
        DB::beginTransaction();

        try {

            if (! $this->logAdminAction($user, 'Updated Milestone', 'Updated #' . $milestone->id)) {
                throw new \Exception('Failed to log admin action.');
            }

            // More specific validation
            if (Milestone::where('milestone', $data['milestone'])->where('category_id', $data['category_id'])->where('milestone_type', $data['milestone_type'])->where('id', '!=', $milestone->id)->exists()) {
                throw new \Exception("The milestone has already been taken.");
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image             = $data['image'];
                unset($data['image']);
            }

            $milestone->update($data);

            if ($milestone) {
                $this->handleImage($image, $milestone->imagePath, $milestone->imageFileName);
            }

            return $this->commitReturn($milestone);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an milestone.
     *
     * @param  array                  $data
     * @param  \App\Models\Milestone  $milestone
     * @return array
     */
    private function populateData($data, $milestone = null)
    {

        if (! isset($data['milestone'])) {
            throw new \Exception('Milestone is required.');
        }

        if (! isset($data['milestone_type'])) {
            throw new \Exception('Milestone type is required.');
        }

        if (isset($data['category_id']) && $data['category_id']) {
            //hoping that since those with no category return null someone doesn't manage to fuck everything up here and somehow return anything other than null.
            $category = $this->milestonePath($data['milestone_type'])::where('id', $data['category_id'])->exists();

            if (! $category) {
                throw new \Exception('The selected milestone category is invalid.');
            }
        }

        /*
            if (isset($data['subcategory_id']) && $data['subcategory_id']) {
                switch ($data['milestone_type']) {
                    case 'Item':
                        $subcategory = ItemSubcategory::where('id', $data['subcategory_id'])->exists();
                        break;
                }
                if (!$subcategory) {
                    throw new \Exception('The selected milestone subcategory is invalid.');
                }
            }

            if ((isset($data['subcategory_id']) && $data['subcategory_id']) && ! ItemSubcategory::where('id', $data['subcategory_id'])->exists()) {
                throw new \Exception('The selected milestone subcategory is invalid.');
            }
        */

        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (isset($data['remove_image'])) {
            if ($milestone && $milestone->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($milestone->imagePath, $milestone->imageFileName);
            }
            unset($data['remove_image']);
        }

        if (! isset($data['is_active'])) {
            $data['is_active'] = 0;
        }

        return $data;
    }

    /**
     * Deletes an milestone.
     *
     * @param  \App\Models\Milestone  $milestone
     * @return bool
     */
    public function deleteMilestone($milestone)
    {
        DB::beginTransaction();

        try {
            // Check first if the milestone is currently owned or if some other site feature uses it
            if (DB::table('user_milestones')->where('milestone_id', $milestone->id)->exists()) {
                throw new \Exception("At least one user completed this milestone.");
            }

            DB::table('user_milestones')->where('milestone_id', $milestone->id)->delete();
            if ($milestone->has_image) {
                $this->deleteImage($milestone->imagePath, $milestone->imageFileName);
            }

            $milestone->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Get category model path
     *
     * @return string
     */
    public function milestonePath($t)
    {
        $type = strtolower($t);
        $conf = config('lorekeeper.milestones.' . $t);
        if (getAssetModelString($type)) {
            $model = getAssetModelString($type);
        } elseif (! getAssetModelString($type) && $conf && $conf['path']) {
            //attempt to get from path
            $model = $conf['path'];
        }

        return $model . 'Category';
    }

    /**
     * Get user's owned items/etc
     *
     * @return object
     */
    public function getOwned($t, $user)
    {
        switch ($t) {
            case 'Item':
                $owned = UserItem::where('user_id', $user->id);
                break;
            case 'Prompt':
                $owned = $user->getSubmissions($user);
                break;
            case 'Character':
                $owned = UserCharacterLog::where('recipient_id', $user->id);
                break;
            case 'Design':
                $owned = Character::whereHas('image', function ($query) use ($user) {
                    $query->whereHas('designers', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                });
                break;
            case 'Currency':
                $owned = CurrencyLog::where('recipient_id', $user->id)->where('recipient_type', 'User')->where('quantity', '>', 0);
                break;
        }
        return $owned;
    }
}
