<?php

namespace App\Http\Controllers;

use App\Models\Character\CharacterCurrency;
use App\Models\Character\CharacterItem;
use App\Models\Encounter\Encounter;
use App\Models\Encounter\EncounterArea;
use App\Models\User\UserCurrency;
use App\Models\User\UserItem;
use App\Services\EncounterService;
use Auth;
use Illuminate\Http\Request;

/**use App\Models\User\UserPet;
use App\Models\User\UserCollection;
use App\Models\User\UserRecipe;
use App\Models\User\UserEnchantment;
use App\Models\User\UserWeapon;
use App\Models\User\UserGear;
use App\Models\User\UserAward;**/

class EncounterController extends Controller {
    /**********************************************************************************************

    ENCOUNTER AREAS

     **********************************************************************************************/

    /**
     * Shows the encounter area index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEncounterAreas() {
        $use_energy = config('lorekeeper.encounters.use_energy');
        $use_characters = config('lorekeeper.encounters.use_characters');
        $user = Auth::user();

        // get energy val
        if ($use_characters) {
            $character = $user->settings->encounterCharacter ?? null;
            if ($use_energy && isset($character)) {
                $energy = $character->encounter_energy;
            } elseif (isset($character)) {
                $energy = optional(CharacterCurrency::where('character_id', $character->id)
                    ->where('currency_id', config('lorekeeper.encounters.energy_replacement_id'))
                    ->first())->quantity ?? 0;
            }
        } else {
            if ($use_energy) {
                $energy = $user->settings->encounter_energy;
            } else {
                $energy = optional(UserCurrency::where('user_id', $user->id)
                    ->where('currency_id', config('lorekeeper.encounters.energy_replacement_id'))
                    ->first())->quantity ?? 0;
            }
        }

        return view('encounters.index', [
            'user'           => $user,
            'areas'          => EncounterArea::orderBy('name', 'DESC')->active()->get(),
            'characters'     => $user->characters()->pluck('slug', 'id'),
            'use_energy'     => $use_energy,
            'use_characters' => $use_characters,
            'energy'         => $energy ?? null,
            'character'      => $character ?? null,
        ]);
    }

    /**
     * explore an area.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exploreArea($id, EncounterService $service) {
        $user = Auth::user();

        $use_characters = config('lorekeeper.encounters.use_characters');

        $area = EncounterArea::active()->find($id);
        if (!$area) {
            abort(404);
        }

        $result = $area->roll(1);
        if (!$result) {
            abort(404);
        }
        $encounter = Encounter::find($result->encounter_id);
        if (!$encounter) {
            abort(404);
        }

        // if ajax passed admin variable and user is staff
        if (request()->has('admin') && $user->isStaff) {
            // do nothing lol
            // skip all the checks to get right to testing
            // oh, we should get the prompts though too
            $selectable = $encounter->prompts;
        } else {
            $selectable = [];
            // if character selection
            if ($use_characters) {
                $character = $user->settings->encounterCharacter;
                if (!$character) {
                    return response()->json(['error' => 'You need to select a character to enter an area.'], 422);
                }

                // if limits, check CHARACTER has them
                if ($area->limits->count()) {
                    if (!$this->checkLimits($user, true, $area, $character)) {
                        return response()->json(['error' => $character->fullName.' does not have the limits to enter this area.'], 422);
                    }
                }

                // if prompt limits, check CHARACTER has them
                foreach ($encounter->prompts as $prompt) {
                    if ($prompt->limits->count()) {
                        $selectable[] = $this->checkLimits($user, true, $prompt, $character, true);
                    } elseif (!$prompt->limits->count()) {
                        // prompts that DON'T have a limit
                        $selectable[] = $prompt;
                    }
                }
            } else {
                // users are set instead

                // if limits, check USER has them
                if ($area->limits->count()) {
                    if (!$this->checkLimits($user, false, $area)) {
                        return response()->json(['error' => 'You do not have the limits to enter this area.'], 422);
                    }
                }

                // if prompt limits, check USER has them
                foreach ($encounter->prompts as $prompt) {
                    if ($prompt->limits->count()) {
                        $selectable[] = $this->checkLimits($user, false, $prompt, null, true);
                    } elseif (!$prompt->limits->count()) {
                        // prompts that DON'T have a limit
                        $selectable[] = $prompt;
                    }
                }
            }
            $selectable = array_filter($selectable);

            if (!$service->beginEncounter($area, $encounter, $user)) {
                return response()->json(['error' => $service->errors()->getMessages()['error'][0]], 422);
            }
        }

        return view('encounters.encounter', [
            'area'           => $area,
            'areas'          => EncounterArea::orderBy('name', 'DESC')->active()->get(),
            'encounter'      => $encounter,
            'action_options' => $selectable,
        ]);
    }

    /**
     * take encounter action.
     *
     * @param App\Services\EncounterService $service
     * @param int|null                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAct(Request $request, EncounterService $service, $id) {
        $data = $request->only(['action', 'area_id', 'encounter_id']);
        if ($id && $service->takeAction(EncounterArea::find($id), $data, Auth::user())) {
            return redirect()->to('encounter-areas');
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Change selected character.
     */
    public function postSelectCharacter(Request $request, EncounterService $service) {
        $id = $request->input('character_id');
        if ($service->selectCharacter(Auth::user(), $id)) {
            flash('Character selected successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    public function checkLimits($user, $use_characters, $object, $character = null, $prompt = null) {
        // let's try and compact some of these checks

        // object is area or prompt
        // check what we should return based on $type

        $use_energy = config('lorekeeper.encounters.use_energy');

        // compacting into one check
        // be careful when setting limits if you intend to use characters, as by default they can't own, and therefore, cannot enter an object with certain limits (such as recipes)
        if ($use_characters) {
            foreach ($object->limits as $limit) {
                $limitType = $limit->item_type;
                $check = null;
                switch ($limitType) {
                    case 'Item':
                        $check = CharacterItem::where('item_id', $limit->item_id)
                            ->where('character_id', $character->id)
                            ->where('count', '>', 0)
                            ->first();
                        break;
                    case 'Currency':
                        $check = CharacterCurrency::where('currency_id', $limit->item_id)
                            ->where('character_id', $character->id)
                            ->where('quantity', '>', 0)
                            ->first();
                        break;
                }
                if (!$check) {
                    return isset($prompt) ? [] : false;
                }
            }
        } else {
            foreach ($object->limits as $limit) {
                $limitType = $limit->item_type;
                $check = null;
                switch ($limitType) {
                    case 'Item':
                        $check = UserItem::where('item_id', $limit->item_id)
                            ->where('user_id', $user->id)
                            ->where('count', '>', 0)
                            ->first();
                        break;
                    case 'Currency':
                        $check = UserCurrency::where('currency_id', $limit->item_id)
                            ->where('user_id', $user->id)
                            ->where('quantity', '>', 0)
                            ->first();
                        break;
                        /**case 'Recipe':
                $check = UserRecipe::where('recipe_id', $limit->item_id)
                ->where('user_id', $user->id)
                ->first();
                break;
                case 'Collection':
                $check = UserCollection::where('collection_id', $limit->item_id)
                ->where('user_id', $user->id)
                ->first();
                break;
                case 'Enchantment':
                $check = UserEnchantment::where('enchantment_id', $limit->item_id)
                ->whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->first();
                break;
                case 'Weapon':
                $check = UserWeapon::where('weapon_id', $limit->item_id)
                ->whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->first();
                break;
                case 'Gear':
                $check = UserGear::where('gear_id', $limit->item_id)
                ->whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->first();
                break;
                case 'Award':
                $check = UserAward::where('award_id', $limit->item_id)
                ->whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->where('count', '>', 0)
                ->first();
                break;
                case 'Pet':
                $check = UserPet::where('pet_id', $limit->item_id)
                ->whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->where('count', '>', 0)
                ->first();
                break;**/
                }

                if (!$check) {
                    return isset($prompt) ? [] : false;
                }
            }
        }

        return isset($prompt) ? $object : true;
    }
}
