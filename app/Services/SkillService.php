<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Skill\SkillCategory;
use App\Models\Skill\Skill;

class SkillService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Skill Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of skill categories and skills.
    |
    */

    /**********************************************************************************************

        SKILL CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Skill\SkillCategory|bool
     */
    public function createSkillCategory($data, $user)
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

            $category = SkillCategory::create($data);

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
     * @param  \App\Models\Skill\SkillCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Skill\SkillCategory|bool
     */
    public function updateSkillCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(SkillCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

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
     * @param  \App\Models\Skill\SkillCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);

        isset($data['is_character_owned']) && $data['is_character_owned'] ? $data['is_character_owned'] : $data['is_character_owned'] = 0;
        isset($data['character_limit']) && $data['character_limit'] ? $data['character_limit'] : $data['character_limit'] = 0;
        isset($data['can_name']) && $data['can_name'] ? $data['can_name'] : $data['can_name'] = 0;

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
     * @param  \App\Models\Skill\SkillCategory  $category
     * @return bool
     */
    public function deleteSkillCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Skill::where('skill_category_id', $category->id)->exists()) throw new \Exception("An skill with this category exists. Please change its category first.");

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
    public function sortSkillCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                SkillCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        SKILLS

    **********************************************************************************************/

    /**
     * Creates a new skill.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Skill\Skill
     */
    public function createSkill($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['skill_category_id']) && $data['skill_category_id'] == 'none') $data['skill_category_id'] = null;
            if(isset($data['parent_id']) && $data['parent_id'] == 'none') $data['parent_id'] = null;
            if(isset($data['prerequisite_id']) && $data['prerequisite_id'] == 'none') $data['prerequisite_id'] = null;
            
            if((isset($data['skill_category_id']) && $data['skill_category_id']) && !SkillCategory::where('id', $data['skill_category_id'])->exists()) throw new \Exception("The selected skill category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $skill = Skill::create($data);

            if ($image) $this->handleImage($image, $skill->imagePath, $skill->imageFileName);

            return $this->commitReturn($skill);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an skill.
     *
     * @param  \App\Models\Skill\Skill  $skill
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Skill\Skill
     */
    public function updateSkill($skill, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['skill_category_id']) && $data['skill_category_id'] == 'none') $data['skill_category_id'] = null;
            if(isset($data['parent_id']) && $data['parent_id'] == 'none') $data['parent_id'] = null;
            if(isset($data['prerequisite_id']) && $data['prerequisite_id'] == 'none') $data['prerequisite_id'] = null;

            // More specific validation
            if(Skill::where('name', $data['name'])->where('id', '!=', $skill->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['skill_category_id']) && $data['skill_category_id']) && !SkillCategory::where('id', $data['skill_category_id'])->exists()) throw new \Exception("The selected skill category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $skill->update($data);

            if ($skill) $this->handleImage($image, $skill->imagePath, $skill->imageFileName);

            return $this->commitReturn($skill);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an skill.
     *
     * @param  array                  $data
     * @param  \App\Models\Skill\Skill  $skill
     * @return array
     */
    private function populateData($data, $skill = null)
    {

        if(isset($data['remove_image']))
        {
            if($skill && $skill->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($skill->imagePath, $skill->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Deletes an skill.
     *
     * @param  \App\Models\Skill\Skill  $skill
     * @return bool
     */
    public function deleteSkill($skill)
    {
        DB::beginTransaction();

        try {
            // Check first if the skill is currently owned or if some other site feature uses it
            if(DB::table('character_skills')->where([['skill_id', '=', $skill->id]])->exists()) throw new \Exception("At least one character currently owns this skill. Please remove the skill(s) before deleting it.");

            DB::table('character_skills')->where('skill_id', $skill->id)->delete();
            if($skill->has_image) $this->deleteImage($skill->imagePath, $skill->imageFileName);
            $skill->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
