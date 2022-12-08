<?php namespace App\Services\Claymore;

use App\Services\Service;

use DB;
use Config;

use App\Models\Claymore\WeaponCategory;
use App\Models\Claymore\Weapon;
use App\Models\Claymore\WeaponStat;

class WeaponService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Weapon Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of weapon categories and weapons.
    |
    */

    /**********************************************************************************************

        WEAPON CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Weapon\WeaponCategory|bool
     */
    public function createWeaponCategory($data, $user)
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

            $category = WeaponCategory::create($data);

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
     * @param  \App\Models\Weapon\WeaponCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Weapon\WeaponCategory|bool
     */
    public function updateWeaponCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(WeaponCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

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
     * @param  \App\Models\Weapon\WeaponCategory|null  $category
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
     * @param  \App\Models\Weapon\WeaponCategory  $category
     * @return bool
     */
    public function deleteWeaponCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Weapon::where('weapon_category_id', $category->id)->exists()) throw new \Exception("A weapon with this category exists. Please change its category first.");

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
    public function sortWeaponCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                WeaponCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        WEAPONS

    **********************************************************************************************/

    /**
     * Creates a new weapon.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Weapon\Weapon
     */
    public function createWeapon($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['weapon_category_id']) && $data['weapon_category_id'] == 'none') $data['weapon_category_id'] = null;
            if(isset($data['parent_id']) && $data['parent_id'] == 'none') $data['parent_id'] = null;
            if(isset($data['currency_id']) && $data['currency_id'] == 'none') $data['currency_id'] = null;
            if((isset($data['weapon_category_id']) && $data['weapon_category_id']) && !WeaponCategory::where('id', $data['weapon_category_id'])->exists()) throw new \Exception("The selected weapon category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $weapon = Weapon::create($data);

            if ($image) $this->handleImage($image, $weapon->imagePath, $weapon->imageFileName);

            return $this->commitReturn($weapon);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an weapon.
     *
     * @param  \App\Models\Weapon\Weapon  $weapon
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Weapon\Weapon
     */
    public function updateWeapon($weapon, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['weapon_category_id']) && $data['weapon_category_id'] == 'none') $data['weapon_category_id'] = null;
            if(isset($data['parent_id']) && $data['parent_id'] == 'none') $data['parent_id'] = null;
            if(isset($data['currency_id']) && $data['currency_id'] == 'none') $data['currency_id'] = null;
            // More specific validation
            if(Weapon::where('name', $data['name'])->where('id', '!=', $weapon->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['weapon_category_id']) && $data['weapon_category_id']) && !WeaponCategory::where('id', $data['weapon_category_id'])->exists()) throw new \Exception("The selected weapon category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $weapon->update($data);

            if ($weapon) $this->handleImage($image, $weapon->imagePath, $weapon->imageFileName);

            return $this->commitReturn($weapon);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an weapon.
     *
     * @param  array                  $data
     * @param  \App\Models\Weapon\Weapon  $weapon
     * @return array
     */
    private function populateData($data, $weapon = null)
    {
        if(!isset($data['allow_transfer'])) $data['allow_transfer'] = 0;

        if(isset($data['remove_image']))
        {
            if($weapon && $weapon->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($weapon->imagePath, $weapon->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Deletes an weapon.
     *
     * @param  \App\Models\Weapon\Weapon  $weapon
     * @return bool
     */
    public function deleteWeapon($weapon)
    {
        DB::beginTransaction();

        try {
            // Check first if the weapon is currently owned or if some other site feature uses it
            if(DB::table('user_weapons')->where('weapon_id', '=', $weapon->id)->where('deleted_at', null)->exists()) throw new \Exception("At least one user currently owns this weapon. Please remove the weapon(s) before deleting it.");
            if(DB::table('loots')->where('rewardable_type', 'Weapon')->where('rewardable_id', $weapon->id)->exists()) throw new \Exception("A loot table currently distributes this weapon as a potential reward. Please remove the weapon before deleting it.");
            if(DB::table('prompt_rewards')->where('rewardable_type', 'Weapon')->where('rewardable_id', $weapon->id)->exists()) throw new \Exception("A prompt currently distributes this weapon as a reward. Please remove the weapon before deleting it.");

            DB::table('user_weapons_log')->where('weapon_id', $weapon->id)->delete();
            DB::table('user_weapons')->where('weapon_id', $weapon->id)->delete();
            if($weapon->has_image) $this->deleteImage($weapon->imagePath, $weapon->imageFileName);

            $weapon->stats()->delete();
            $weapon->delete();

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
            $weapon = Weapon::find($id);
            $weapon->stats()->delete();
            
            if(isset($data['stats']))
            {
                foreach($data['stats'] as $key=>$stat)
                {
                    if($stat != null && $stat > 0) {
                        WeaponStat::create([
                            'weapon_id' => $id,
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
