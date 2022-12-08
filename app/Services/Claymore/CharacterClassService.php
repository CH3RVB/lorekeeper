<?php namespace App\Services\Claymore;

use App\Services\Service;

use DB;
use Config;

use App\Models\Character\Character;
use App\Models\Character\CharacterClass;
use App\Models\Claymore\WeaponCategory;
use App\Models\Claymore\GearCategory;

class CharacterClassService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Character Class Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of character categories.
    |
    */

    /**
     * Create a class.
     *
     * @param  array  $data
     * @return \App\Models\Character\CharacterClass|bool
     */
    public function createCharacterClass($data)
    {
        DB::beginTransaction();

        try {
            $data = $this->populateClassData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $class = CharacterClass::create($data);

            if ($image) $this->handleImage($image, $class->classImagePath, $class->classImageFileName);

            return $this->commitReturn($class);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a class.
     *
     * @param  \App\Models\Character\CharacterClass  $class
     * @param  array                                    $data
     * @return \App\Models\Character\CharacterClass|bool
     */
    public function updateCharacterClass($class, $data)
    {
        DB::beginTransaction();

        try {
            if(CharacterClass::where('name', $data['name'])->where('id', '!=', $class->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateClassData($data, $class);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $class->update($data);

            if ($class) $this->handleImage($image, $class->classImagePath, $class->classImageFileName);

            return $this->commitReturn($class);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle class data.
     *
     * @param  array                                         $data
     * @param  \App\Models\Character\CharacterClass|null  $class
     * @return array
     */
    private function populateClassData($data, $class = null)
    {
        
        if(isset($data['remove_image']))
        {
            if($class && $class->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($class->classImagePath, $class->classImageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Delete a class.
     *
     * @param  \App\Models\Character\CharacterClass  $class
     * @return bool
     */
    public function deleteCharacterClass($class)
    {
        DB::beginTransaction();

        try {
            // Check first if the class is currently in use
            if(GearCategory::where('class_restriction', $class->id)->exists()) throw new \Exception("A gear class with this restriction exists. Please change its class first.");
            if(WeaponCategory::where('class_restriction', $class->id)->exists()) throw new \Exception("A weapon class with this restriction exists. Please change its class first.");
            if(Character::where('class_id', $class->id)->exists()) throw new \Exception("An character with this class exists. Please change its class first.");
            
            if($class->has_image) $this->deleteImage($class->classImagePath, $class->classImageFileName); 
            $class->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}