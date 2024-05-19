<?php namespace App\Services\Item;

use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Item\Item;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\CharacterManager;
use App\Services\InventoryManager;
use App\Services\Service;
use DB;

class RandomslotService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Random Slot Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of random slot type items.
    |
     */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [
            'rarities' => ['0' => 'Select Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses' => ['0' => 'Select Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes' => Subtype::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'traits' => ['0' => 'Select Trait'] + Feature::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['0' => 'Select Trait Category'] + FeatureCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'isMyo' => true,
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        //fetch data from DB, if there is no data then set to NULL instead
        $characterData['name'] = isset($tag->data['name']) ? $tag->data['name'] : null;
        $characterData['species_id'] = isset($tag->data['species_id']) && $tag->data['species_id'] ? $tag->data['species_id'] : null;
        $characterData['rarity_id'] = isset($tag->data['rarity_id']) && $tag->data['rarity_id'] ? $tag->data['rarity_id'] : null;
        $characterData['description'] = isset($tag->data['description']) && $tag->data['description'] ? $tag->data['description'] : null;
        $characterData['parsed_description'] = parse($characterData['description']);
        $characterData['sale_value'] = isset($tag->data['sale_value']) ? $tag->data['sale_value'] : 0;
        //the switches hate true/false, need to convert boolean to binary
        if (isset($tag->data['is_sellable']) && $tag->data['is_sellable'] == 'true') {
            $characterData['is_sellable'] = 1;
        } else {
            $characterData['is_sellable'] = 0;
        }

        if (isset($tag->data['is_tradeable']) && $tag->data['is_tradeable'] == 'true') {
            $characterData['is_tradeable'] = 1;
        } else {
            $characterData['is_tradeable'] = 0;
        }

        if (isset($tag->data['is_giftable']) && $tag->data['is_giftable'] == 'true') {
            $characterData['is_giftable'] = 1;
        } else {
            $characterData['is_giftable'] = 0;
        }

        if (isset($tag->data['is_visible']) && $tag->data['is_visible'] == 'true') {
            $characterData['is_visible'] = 1;
        } else {
            $characterData['is_visible'] = 0;
        }

        return $characterData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for DB storage.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        //put inputs into an array to transfer to the DB
        $characterData['name'] = isset($data['name']) ? $data['name'] : null;
        $characterData['species_id'] = isset($data['species_id']) && $data['species_id'] ? $data['species_id'] : null;
        $characterData['rarity_id'] = isset($data['rarity_id']) && $data['rarity_id'] ? $data['rarity_id'] : null;
        $characterData['description'] = isset($data['description']) && $data['description'] ? $data['description'] : null;
        $characterData['parsed_description'] = parse($characterData['description']);
        $characterData['sale_value'] = isset($data['sale_value']) ? $data['sale_value'] : 0;
        //if the switch was toggled, set true, if null, set false
        $characterData['is_sellable'] = isset($data['is_sellable']);
        $characterData['is_tradeable'] = isset($data['is_tradeable']);
        $characterData['is_giftable'] = isset($data['is_giftable']);
        $characterData['is_visible'] = isset($data['is_visible']);

        //process the hell that is the subtypes
        $subtypes = [];
        if (isset($data['subtype_type'])) {
            foreach ($data['subtype_type'] as $key => $type) {
                if (!$type) {
                    throw new \Exception('Subtype type is required.');
                }

                if (!$data['subtype_id'][$key]) {
                    throw new \Exception('You did not specify a subtype.');
                }

                if (!$data['sub_weight'][$key] || $data['sub_weight'][$key] < 1) {
                    throw new \Exception('Weight is required and must be an integer greater than 0.');
                }

                //check the subtype actually exists (if none is not selected)
                if ($type == 'Subtype') {
                    $subtype = Subtype::find($data['subtype_id'][$key]);
                    if (!(isset($data['species_id']) && $data['species_id'])) {
                        throw new \Exception('Species must be selected to select a subtype.');
                    }
                    if (!$subtype || $subtype->species_id != $data['species_id']) {
                        throw new \Exception('Selected subtype invalid or does not match species.');
                    }
                }

                $subtypes[] = (object) [
                    'subtype_type' => $type,
                    'subtype_id' => isset($data['subtype_id'][$key]) ? $data['subtype_id'][$key] : 1,
                    'weight' => $data['sub_weight'][$key],
                ];
            }
            $characterData['subtypes'] = $subtypes;
        } else {
            $characterData['subtypes'] = null;
        }

        //then process the even hell-er hell that is the groups and everything within

        $groups = [];
        if (isset($data['group_name'])) {
            foreach ($data['group_name'] as $key => $id) {
                if (!$data['group_name'][$key]) {
                    throw new \Exception('One or more of the groups was not given an internal name.');
                }

                if (!$data['trait_min'][$key] || $data['trait_min'][$key] < 1) {
                    throw new \Exception('One or more of the groups was not given a trait minimum or was set to 0 or less.');
                }

                if (!$data['trait_max'][$key] || $data['trait_max'][$key] < 1) {
                    throw new \Exception('One or more of the groups was not given a trait maximum or was set to 0 or less.');
                }

                //process the traits
                $traitIds = [];
                if (isset($data['trait_id'][$id])) {
                    foreach ($data['trait_id'][$id] as $trait => $c) {
                        //check the trait actually exists
                        $feature = Feature::find($c);
                        if (!$feature) {
                            throw new \Exception('One or more traits selected do not exist.');
                        }

                        //check the weight is valid
                        if (!$data['weight'][$id][$trait] || $data['weight'][$id][$trait] < 1) {
                            throw new \Exception('Weight is required and must be an integer greater than 0.');
                        }

                        //check it's not species exclusive (if species set and species does not match)
                        if ($characterData['species_id'] && $feature->species_id) {
                            if ($feature->species_id != $characterData['species_id']) {
                                //filter bc i don't feel like putting people through the nightmare of making 1 mistake and it vanishes
                                continue;
                            }
                        }
                        $traitIds[] = (object) [
                            'trait_id' => $c,
                            'weight' => $data['weight'][$id][$trait],
                        ];
                    }
                } else {
                    throw new \Exception('Cannot roll a group with no traits to roll.');
                }

                $groups[] = (object) [
                    'group_name' => $data['group_name'][$key],
                    'trait_min' => $data['trait_min'][$key],
                    'trait_max' => $data['trait_max'][$key],
                    'traits' => $traitIds,
                ];
            }
            $characterData['groups'] = $groups;
        } else {
            throw new \Exception('You cannot make a randomized MYO without a group to randomize...');
        }

        DB::beginTransaction();

        try {
            //get characterData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($characterData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function act($stacks, $user, $data, $test = null)
    {
        DB::beginTransaction();

        try {
            foreach ($stacks as $key => $stack) {
                // We don't want to let anyone who isn't the owner of the slot to use it,
                // so do some validation...
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Next, try to delete the tag item. If successful, we can start distributing rewards.
                if ((new InventoryManager())->debitStack($stack->user, 'Slot Used', ['data' => ''], $stack, $data['quantities'][$key])) {
                    $this->generateMYO($stack->item->tag('randomslot')->data, $data['quantities'][$key], $user, false);
                }
            }
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Randomize the stock
     *
     */
    public function generateMYO($stack, $quantity, $user, $test = null)
    {
        try {
            if ($test) {
                $testMyos = [];
            }
            //a lot of fiddling around just so i can reuse this for the test roller. lmao.
            for ($q = 0; $q < $quantity; $q++) {
                //fill an array with the DB contents
                $characterData = $stack;
                //set user who is opening the item
                $characterData['user_id'] = $user->id;
                //other vital data that is default
                $characterData['name'] = isset($characterData['name']) ? $characterData['name'] : 'Slot';
                $characterData['transferrable_at'] = null;
                $characterData['is_myo_slot'] = 1;
                //this uses your default MYO slot image from the CharacterManager
                //see wiki page for documentation on adding a default image switch

                $characterData['use_cropper'] = 0;
                $characterData['x0'] = null;
                $characterData['x1'] = null;
                $characterData['y0'] = null;
                $characterData['y1'] = null;
                $characterData['image'] = null;
                $characterData['thumbnail'] = null;

                $characterData['artist_id'][0] = null;
                $characterData['artist_url'][0] = null;
                $characterData['designer_id'][0] = null;
                $characterData['designer_url'][0] = null;
                //begin the cursed process of assigning traits :')

                //roll subtype if applicable
                if (isset($stack['subtypes'])) {
                    $subtypes = $stack['subtypes'];
                    $totalWeight = 0;
                    foreach ($subtypes as $l) {
                        $totalWeight += $l['weight'];
                    }

                    $result = $this->weightedRoll($subtypes, $totalWeight);

                    if ($result) {
                        //skip if it's none
                        if ($result['subtype_type'] != "None") {
                            //get result
                            $result = $result['subtype_id'];
                            //prep subtype to be applied

                            //check it exists
                            $subtype = Subtype::find($result);
                            if (!$subtype) {
                                throw new \Exception('Invalid subtype rolled.');
                            }
                            $characterData['subtype_id'] = $subtype->id;
                        }

                    } else {
                        throw new \Exception('Error orcurred while rolling subtype.');
                    }
                }

                //take the groups and process them
                $finaltraits = [];
                $featdata = [];
                if (isset($characterData['groups'])) {
                    foreach ($characterData['groups'] as $group) {
                        //get min and max
                        //rand it
                        $qty = mt_rand($group['trait_min'], $group['trait_max']);

                        if (isset($group['traits'])) {
                            $grouptraits = $group['traits'];
                            $totalWeight = 0;
                            foreach ($grouptraits as $l) {
                                $totalWeight += $l['weight'];
                            }
                            //for qty count...
                            for ($i = 1; $i <= $qty; $i++) {
                                $result = $this->weightedRoll($grouptraits, $totalWeight);

                                if ($result) {
                                    //get result
                                    $result = $result['trait_id'];
                                    //prep trait to be applied

                                    //check it exists
                                    $feature = Feature::find($result);
                                    if (!$feature) {
                                        throw new \Exception('Invalid trait rolled.');
                                    }
                                    $finaltraits[] = $feature->id;
                                    $featdata[] = null;
                                } else {
                                    throw new \Exception('Error orcurred while rolling traits.');
                                }
                            }
                        } else {
                            throw new \Exception('Cannot roll a group with no traits to roll.');
                        }
                    }
                }

                //make trait array and filter dupes
                $characterData['feature_id'] = array_unique($finaltraits);
                $characterData['feature_data'] = $featdata;

                //DB has 'true' and 'false' as strings, so need to set them to true/null
                if ($stack['is_sellable'] == 'true') {
                    $characterData['is_sellable'] = true;
                } else {
                    $characterData['is_sellable'] = null;
                }

                if ($stack['is_tradeable'] == 'true') {
                    $characterData['is_tradeable'] = true;
                } else {
                    $characterData['is_tradeable'] = null;
                }

                if ($stack['is_giftable'] == 'true') {
                    $characterData['is_giftable'] = true;
                } else {
                    $characterData['is_giftable'] = null;
                }

                if ($stack['is_visible'] == 'true') {
                    $characterData['is_visible'] = true;
                } else {
                    $characterData['is_visible'] = null;
                }

                if (!$test) {
                    //create myo
                    $charService = new CharacterManager();
                    if ($character = $charService->createCharacter($characterData, $user, true)) {
                        flash('<a href="' . $character->url . '">MYO slot</a> created successfully.')->success();
                    } else {
                        throw new \Exception('Failed to use slot.');
                    }
                } else {
                    $testMyos[] = [
                        'species' => Species::find($characterData['species_id']),
                        'subtype' => isset($characterData['subtype_id']) ? Subtype::find($characterData['subtype_id']) : null,
                        'rarity' => isset($characterData['rarity_id']) ? Rarity::find($characterData['rarity_id']) : null,
                        'features' => $finaltraits,
                    ];
                }
            }
            if ($test) {
                return $testMyos;
            } else {
                return $this->commitReturn(true);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * Do a weighted roll
     *
     */
    public function weightedRoll($group, $totalWeight)
    {
        try {
            $roll = mt_rand(0, $totalWeight - 1);
            $result = null;
            $prev = null;
            $count = 0;
            foreach ($group as $l) {
                $count += $l['weight'];

                if ($roll < $count) {
                    $result = $l;
                    break;
                }
                $prev = $l;
            }
            if (!$result) {
                $result = $prev;
            }

            return $result;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
