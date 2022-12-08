<?php namespace App\Services\Claymore;

use App\Services\Service;

use DB;
use Config;

use App\Models\Claymore\GearCategory;
use App\Models\Claymore\Gear;
use App\Models\Claymore\GearStat;

class GearService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Gear Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of gear categories and gears.
    |
    */

    /**********************************************************************************************

        GEAR CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Gear\GearCategory|bool
     */
    public function createGearCategory($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateCategoryData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $category = GearCategory::create($data);

            if ($image) $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param  \App\Models\Gear\GearCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Gear\GearCategory|bool
     */
    public function updateGearCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(GearCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateCategoryData($data, $category);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $category->update($data);

            if ($category) $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param  array                               $data
     * @param  \App\Models\Gear\GearCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
        if(isset($data['class_restriction']) && $data['class_restriction'] == 'none') $data['class_restriction'] = null;

        if(isset($data['remove_image']))
        {
            if($category && $category->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Delete a category.
     *
     * @param  \App\Models\Gear\GearCategory  $category
     * @return bool
     */
    public function deleteGearCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Gear::where('gear_category_id', $category->id)->exists()) throw new \Exception("A gear with this category exists. Please change its category first.");

            if($category->has_image) $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            $category->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortGearCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                GearCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        GEARS

    **********************************************************************************************/

    /**
     * Creates a new gear.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Gear\Gear
     */
    public function createGear($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['gear_category_id']) && $data['gear_category_id'] == 'none') $data['gear_category_id'] = null;
            if(isset($data['parent_id']) && $data['parent_id'] == 'none') $data['parent_id'] = null;
            if(isset($data['currency_id']) && $data['currency_id'] == 'none') $data['currency_id'] = null;


            if((isset($data['gear_category_id']) && $data['gear_category_id']) && !GearCategory::where('id', $data['gear_category_id'])->exists()) throw new \Exception("The selected gear category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $gear = Gear::create($data);

            if ($image) $this->handleImage($image, $gear->imagePath, $gear->imageFileName);

            return $this->commitReturn($gear);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an gear.
     *
     * @param  \App\Models\Gear\Gear  $gear
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Gear\Gear
     */
    public function updateGear($gear, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['gear_category_id']) && $data['gear_category_id'] == 'none') $data['gear_category_id'] = null;
            if(isset($data['parent_id']) && $data['parent_id'] == 'none') $data['parent_id'] = null;
            if(isset($data['currency_id']) && $data['currency_id'] == 'none') $data['currency_id'] = null;


            // More specific validation
            if(Gear::where('name', $data['name'])->where('id', '!=', $gear->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['gear_category_id']) && $data['gear_category_id']) && !GearCategory::where('id', $data['gear_category_id'])->exists()) throw new \Exception("The selected gear category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $gear->update($data);

            if ($gear) $this->handleImage($image, $gear->imagePath, $gear->imageFileName);

            return $this->commitReturn($gear);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an gear.
     *
     * @param  array                  $data
     * @param  \App\Models\Gear\Gear  $gear
     * @return array
     */
    private function populateData($data, $gear = null)
    {
        if(!isset($data['allow_transfer'])) $data['allow_transfer'] = 0;

        if(isset($data['remove_image']))
        {
            if($gear && $gear->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($gear->imagePath, $gear->imageFileName);
            }
            unset($data['remove_image']);
        }
        
        return $data;
    }

    /**
     * Deletes an gear.
     *
     * @param  \App\Models\Gear\Gear  $gear
     * @return bool
     */
    public function deleteGear($gear)
    {
        DB::beginTransaction();

        try {
            // Check first if the gear is currently owned or if some other site feature uses it
            if(DB::table('user_gears')->where('gear_id', '=', $gear->id)->where('deleted_at', null)->exists()) throw new \Exception("At least one user currently owns this gear. Please remove the gear(s) before deleting it.");
            if(DB::table('loots')->where('rewardable_type', 'Gear')->where('rewardable_id', $gear->id)->exists()) throw new \Exception("A loot table currently distributes this gear as a potential reward. Please remove the gear before deleting it.");
            if(DB::table('prompt_rewards')->where('rewardable_type', 'Gear')->where('rewardable_id', $gear->id)->exists()) throw new \Exception("A prompt currently distributes this gear as a reward. Please remove the gear before deleting it.");

            DB::table('user_gears_log')->where('gear_id', $gear->id)->delete();
            DB::table('user_gears')->where('gear_id', $gear->id)->delete();
            if($gear->has_image) $this->deleteImage($gear->imagePath, $gear->imageFileName);
            
            $gear->stats()->delete();
            $gear->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function editStats($data, $id)
    {
        DB::beginTransaction();

        try {
            $gear = Gear::find($id);
            $gear->stats()->delete();
            
            if(isset($data['stats']))
            {
                foreach($data['stats'] as $key=>$stat)
                {
                    if($stat != null && $stat > 0) {
                        GearStat::create([
                            'gear_id' => $id,
                            'stat_id' => $key,
                            'count' => $stat,
                        ]);
                    }
                }
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
