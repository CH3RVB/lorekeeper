<?php namespace App\Services\Claymore;

use App\Services\Service;

use DB;
use Config;

use App\Models\Claymore\EnchantmentCategory;
use App\Models\Claymore\Enchantment;
use App\Models\Claymore\EnchantmentStat;

class EnchantmentService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Enchantment Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of enchantment categories and enchantments.
    |
    */

    /**********************************************************************************************

        Enchantment CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Enchantment\EnchantmentCategory|bool
     */
    public function createEnchantmentCategory($data, $user)
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

            $category = EnchantmentCategory::create($data);

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
     * @param  \App\Models\Enchantment\EnchantmentCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Enchantment\EnchantmentCategory|bool
     */
    public function updateEnchantmentCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(EnchantmentCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

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
     * @param  \App\Models\Enchantment\EnchantmentCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
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
     * @param  \App\Models\Enchantment\EnchantmentCategory  $category
     * @return bool
     */
    public function deleteEnchantmentCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Enchantment::where('enchantment_category_id', $category->id)->exists()) throw new \Exception("A enchantment with this category exists. Please change its category first.");

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
    public function sortEnchantmentCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                EnchantmentCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        EnchantmentS

    **********************************************************************************************/

    /**
     * Creates a new enchantment.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Enchantment\Enchantment
     */
    public function createEnchantment($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['enchantment_category_id']) && $data['enchantment_category_id'] == 'none') $data['enchantment_category_id'] = null;
            if(isset($data['currency_id']) && $data['currency_id'] == 'none') $data['currency_id'] = null;


            if((isset($data['enchantment_category_id']) && $data['enchantment_category_id']) && !EnchantmentCategory::where('id', $data['enchantment_category_id'])->exists()) throw new \Exception("The selected enchantment category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $enchantment = Enchantment::create($data);

            if ($image) $this->handleImage($image, $enchantment->imagePath, $enchantment->imageFileName);

            return $this->commitReturn($enchantment);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an enchantment.
     *
     * @param  \App\Models\Enchantment\Enchantment  $enchantment
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Enchantment\Enchantment
     */
    public function updateEnchantment($enchantment, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['enchantment_category_id']) && $data['enchantment_category_id'] == 'none') $data['enchantment_category_id'] = null;
            if(isset($data['currency_id']) && $data['currency_id'] == 'none') $data['currency_id'] = null;


            // More specific validation
            if(Enchantment::where('name', $data['name'])->where('id', '!=', $enchantment->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['enchantment_category_id']) && $data['enchantment_category_id']) && !EnchantmentCategory::where('id', $data['enchantment_category_id'])->exists()) throw new \Exception("The selected enchantment category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $enchantment->update($data);

            if ($enchantment) $this->handleImage($image, $enchantment->imagePath, $enchantment->imageFileName);

            return $this->commitReturn($enchantment);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an enchantment.
     *
     * @param  array                  $data
     * @param  \App\Models\Enchantment\Enchantment  $enchantment
     * @return array
     */
    private function populateData($data, $enchantment = null)
    {

        if(isset($data['remove_image']))
        {
            if($enchantment && $enchantment->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($enchantment->imagePath, $enchantment->imageFileName);
            }
            unset($data['remove_image']);
        }
        
        return $data;
    }

    /**
     * Deletes an enchantment.
     *
     * @param  \App\Models\Enchantment\Enchantment  $enchantment
     * @return bool
     */
    public function deleteEnchantment($enchantment)
    {
        DB::beginTransaction();

        try {
            // Check first if the enchantment is currently owned or if some other site feature uses it
            if(DB::table('user_enchantments')->where('enchantment_id', '=', $enchantment->id)->where('deleted_at', null)->exists()) throw new \Exception("At least one user currently owns this enchantment. Please remove the enchantment(s) before deleting it.");

            DB::table('user_enchantments_log')->where('enchantment_id', $enchantment->id)->delete();
            DB::table('user_enchantments')->where('enchantment_id', $enchantment->id)->delete();
            if($enchantment->has_image) $this->deleteImage($enchantment->imagePath, $enchantment->imageFileName);
            
            $enchantment->stats()->delete();
            $enchantment->delete();

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
            $enchantment = Enchantment::find($id);
            $enchantment->stats()->delete();
            
            if(isset($data['stats']))
            {
                foreach($data['stats'] as $key=>$stat)
                {
                    if($stat != null && $stat > 0) {
                        EnchantmentStat::create([
                            'enchantment_id' => $id,
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
