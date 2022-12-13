<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Config;

use App\Models\Currency\Currency;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Item\ItemCategory;
use App\Models\Item\Item;
use App\Models\Pet\PetCategory;
use App\Models\Pet\Pet;
use App\Models\Skill\SkillCategory;
use App\Models\Skill\Skill;
use App\Models\Feature\FeatureCategory;
use App\Models\Feature\Feature;
use App\Models\Character\CharacterCategory;
use App\Models\Prompt\PromptCategory;
use App\Models\Prompt\Prompt;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;
use App\Models\User\User;
use App\Models\Level\Level;
use App\Models\Level\CharacterLevel;
use App\Models\Level\UserLevel;
use App\Models\Stat\Stat;

use App\Models\Claymore\WeaponCategory;
use App\Models\Claymore\Weapon;
use App\Models\Claymore\GearCategory;
use App\Models\Claymore\Gear;
use App\Models\Claymore\EnchantmentCategory;
use App\Models\Claymore\Enchantment;
use App\Models\Character\CharacterClass;

class WorldController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | World Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about the world, as entered in the admin panel.
    | Pages displayed by this controller form the site's encyclopedia.
    |
    */

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('world.index');
    }

    /**
     * Shows the currency page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrencies(Request $request)
    {
        $query = Currency::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('abbreviation', 'LIKE', '%'.$name.'%');
        return view('world.currencies', [
            'currencies' => $query->orderBy('name')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the rarity page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRarities(Request $request)
    {
        $query = Rarity::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.rarities', [
            'rarities' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the species page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSpecieses(Request $request)
    {
        $query = Species::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.specieses', [
            'specieses' => $query->with(['subtypes' => function($query) {
                $query->orderBy('sort', 'DESC');
            }])->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the subtypes page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubtypes(Request $request)
    {
        $query = Subtype::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.subtypes', [
            'subtypes' => $query->with('species')->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the item categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItemCategories(Request $request)
    {
        $query = ItemCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.item_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the trait categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatureCategories(Request $request)
    {
        $query = FeatureCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.feature_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the traits page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatures(Request $request)
    {
        $query = Feature::with('category')->with('rarity')->with('species');
        $data = $request->only(['rarity_id', 'feature_category_id', 'species_id', 'name', 'sort']);
        if(isset($data['rarity_id']) && $data['rarity_id'] != 'none')
            $query->where('rarity_id', $data['rarity_id']);
        if(isset($data['feature_category_id']) && $data['feature_category_id'] != 'none')
            $query->where('feature_category_id', $data['feature_category_id']);
        if(isset($data['species_id']) && $data['species_id'] != 'none')
            $query->where('species_id', $data['species_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'rarity':
                    $query->sortRarity();
                    break;
                case 'rarity-reverse':
                    $query->sortRarity(true);
                    break;
                case 'species':
                    $query->sortSpecies();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        }
        else $query->sortCategory();

        return view('world.features', [
            'features' => $query->paginate(20)->appends($request->query()),
            'rarities' => ['none' => 'Any Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses' => ['none' => 'Any Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'Any Category'] + FeatureCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows a species' visual trait list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSpeciesFeatures($id)
    {
        $categories = FeatureCategory::orderBy('sort', 'DESC')->get();
        $rarities = Rarity::orderBy('sort', 'ASC')->get();
        $species = Species::where('id', $id)->first();
        if(!$species) abort(404);
        if(!Config::get('lorekeeper.extensions.species_trait_index')) abort(404);

        $features = count($categories) ?
            $species->features()
                ->orderByRaw('FIELD(feature_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')
                ->orderByRaw('FIELD(rarity_id,'.implode(',', $rarities->pluck('id')->toArray()).')')
                ->orderBy('has_image', 'DESC')
                ->orderBy('name')
                ->get()
                ->groupBy(['feature_category_id', 'id']) :
            $species->features()
                ->orderByRaw('FIELD(rarity_id,'.implode(',', $rarities->pluck('id')->toArray()).')')
                ->orderBy('has_image', 'DESC')
                ->orderBy('name')
                ->get()
                ->groupBy(['feature_category_id', 'id']);

        return view('world.species_features', [
            'species' => $species,
            'categories' => $categories->keyBy('id'),
            'rarities' => $rarities->keyBy('id'),
            'features' => $features,
        ]);
    }

    /**
     * Shows the items page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItems(Request $request)
    {
        $query = Item::with('category')->released();
        $data = $request->only(['item_category_id', 'name', 'sort', 'artist']);
        if(isset($data['item_category_id']) && $data['item_category_id'] != 'none')
            $query->where('item_category_id', $data['item_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        if(isset($data['artist']) && $data['artist'] != 'none')
            $query->where('artist_id', $data['artist']);

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        }
        else $query->sortCategory();

        return view('world.items', [
            'items' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + ItemCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'shops' => Shop::orderBy('sort', 'DESC')->get(),
            'artists' => ['none' => 'Any Artist'] + User::whereIn('id', Item::whereNotNull('artist_id')->pluck('artist_id')->toArray())->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows an individual item's page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItem($id)
    {
        $categories = ItemCategory::orderBy('sort', 'DESC')->get();
        $item = Item::where('id', $id)->released()->first();
        if(!$item) abort(404);

        return view('world.item_page', [
            'item' => $item,
            'imageUrl' => $item->imageUrl,
            'name' => $item->displayName,
            'description' => $item->parsed_description,
            'categories' => $categories->keyBy('id'),
            'shops' => Shop::whereIn('id', ShopStock::where('item_id', $item->id)->pluck('shop_id')->unique()->toArray())->orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the character categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterCategories(Request $request)
    {
        $query = CharacterCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('code', 'LIKE', '%'.$name.'%');
        return view('world.character_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the prompt categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPromptCategories(Request $request)
    {
        $query = PromptCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.prompt_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the prompts page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPrompts(Request $request)
    {
        $query = Prompt::active()->with('category');
        $data = $request->only(['prompt_category_id', 'name', 'sort']);
        if(isset($data['prompt_category_id']) && $data['prompt_category_id'] != 'none')
            $query->where('prompt_category_id', $data['prompt_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
                case 'start':
                    $query->sortStart();
                    break;
                case 'start-reverse':
                    $query->sortStart(true);
                    break;
                case 'end':
                    $query->sortEnd();
                    break;
                case 'end-reverse':
                    $query->sortEnd(true);
                    break;
            }
        }
        else $query->sortCategory();

        return view('world.prompts', [
            'prompts' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + PromptCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }
    /**
     * 
     *  LEVELS
     * 
     */
    public function getLevels()
    {
        return view('world.level_index');
    }

    /**
     * Level types
     */
    public function getLevelTypes($type)
    {
        if($type == 'user')
        {
            $levels = Level::where('level_type', 'User')->get();
        }
        elseif($type == 'character')
        {
            $levels = Level::where('level_type', 'Character')->get();
        }
        else abort(404);

        return view('world.level_type_index', [
            'levels' => $levels->paginate(20),
            'type' => $type
        ]);
    }

    /**
     * ID view
     */
    public function getSingleLevel($type, $level)
    {
        if($type == 'user')
        {
            $levels = Level::where('level', $level)->where('level_type', 'User')->first();
        }
        elseif($type == 'character')
        {
            $levels = Level::where('level', $level)->where('level_type', 'Character')->first();
        }
        else abort(404);

        return view('world.level_single', [
            'level' => $levels,
            'type' => $type
        ]);
    }

    /**
     * STATS
     */
    public function getStats()
    {
        $stats = Stat::all();

        return view('world.stats', [
            'stats' => $stats,
        ]);
    }

    /**
     * Shows the skill categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSkillCategories(Request $request)
    {
        $query = SkillCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.skill_categories', [
            'categories' => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the skills page.
     *      
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSkills(Request $request)
    {
        $query = Skill::with('category');
        $data = $request->only(['skill_category_id', 'name', 'sort']);
        if(isset($data['skill_category_id']) && $data['skill_category_id'] != 'none')
            $query->where('skill_category_id', $data['skill_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        return view('world.skills', [
            'skills' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + SkillCategory::pluck('name', 'id')->toArray(),
        ]);
    }
    
    /**
     * Shows an individual skill's page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSkill($id)
    {
        $categories = SkillCategory::get();
        $skill = Skill::where('id', $id)->first();
        if(!$skill) abort(404);

        return view('world.skill_page', [
            'skill' => $skill,
            'imageUrl' => $skill->imageUrl,
            'name' => $skill->displayName,
            'description' => $skill->parsed_description,
            'categories' => $categories->keyBy('id'),
        ]);
    }

    /**
     * Shows the pet categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPetCategories(Request $request)
    {
        $query = PetCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.pet_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /** 
    * Shows the pets page.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Contracts\Support\Renderable
    */
   public function getPets(Request $request)
   {
       $query = Pet::with('category');
       $data = $request->only(['pet_category_id', 'name', 'sort']);
       if(isset($data['pet_category_id']) && $data['pet_category_id'] != 'none') 
           $query->where('pet_category_id', $data['pet_category_id']);
       if(isset($data['name'])) 
           $query->where('name', 'LIKE', '%'.$data['name'].'%');

       if(isset($data['sort'])) 
       {
           switch($data['sort']) {
               case 'alpha':
                   $query->sortAlphabetical();
                   break;
               case 'alpha-reverse':
                   $query->sortAlphabetical(true);
                   break;
               case 'category':
                   $query->sortCategory();
                   break;
               case 'newest':
                   $query->sortNewest();
                   break;
               case 'oldest':
                   $query->sortOldest();
                   break;
           }
       } 
       else $query->sortCategory();

       return view('world.pets', [
           'pets' => $query->paginate(20)->appends($request->query()),
           'categories' => ['none' => 'Any Category'] + PetCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
       ]);
    }    

    /**
     * Shows the weapon categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWeaponCategories(Request $request)
    {
        $query = WeaponCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.weapon_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /** 
    * Shows the weapons page.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Contracts\Support\Renderable
    */
   public function getWeapons(Request $request)
   {
       $query = Weapon::with('category');
       $data = $request->only(['weapon_category_id', 'name', 'sort']);
       if(isset($data['weapon_category_id']) && $data['weapon_category_id'] != 'none') 
           $query->where('weapon_category_id', $data['weapon_category_id']);
       if(isset($data['name'])) 
           $query->where('name', 'LIKE', '%'.$data['name'].'%');

       if(isset($data['sort'])) 
       {
           switch($data['sort']) {
               case 'alpha':
                   $query->sortAlphabetical();
                   break;
               case 'alpha-reverse':
                   $query->sortAlphabetical(true);
                   break;
               case 'category':
                   $query->sortCategory();
                   break;
               case 'newest':
                   $query->sortNewest();
                   break;
               case 'oldest':
                   $query->sortOldest();
                   break;
           }
       } 
       else $query->sortCategory();

       return view('world.weapons', [
           'weapons' => $query->paginate(20)->appends($request->query()),
           'categories' => ['none' => 'Any Category'] + WeaponCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
       ]);
    }    

    /**
     * Shows an individual weapon's page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWeapon($id)
    {
        $categories = WeaponCategory::orderBy('sort', 'DESC')->get();
        $weapon = Weapon::where('id', $id)->first();
        if(!$weapon) abort(404);

        return view('world.weapon_page', [
            'weapon' => $weapon,
            'imageUrl' => $weapon->imageUrl,
            'name' => $weapon->displayName,
            'description' => $weapon->parsed_description,
            'categories' => $categories->keyBy('id'),
        ]);
    }

    /**
     * Shows the gear categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGearCategories(Request $request)
    {
        $query = GearCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.gear_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /** 
    * Shows the gears page.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Contracts\Support\Renderable
    */
   public function getGears(Request $request)
   {
       $query = Gear::with('category');
       $data = $request->only(['gear_category_id', 'name', 'sort']);
       if(isset($data['gear_category_id']) && $data['gear_category_id'] != 'none') 
           $query->where('gear_category_id', $data['gear_category_id']);
       if(isset($data['name'])) 
           $query->where('name', 'LIKE', '%'.$data['name'].'%');

       if(isset($data['sort'])) 
       {
           switch($data['sort']) {
               case 'alpha':
                   $query->sortAlphabetical();
                   break;
               case 'alpha-reverse':
                   $query->sortAlphabetical(true);
                   break;
               case 'category':
                   $query->sortCategory();
                   break;
               case 'newest':
                   $query->sortNewest();
                   break;
               case 'oldest':
                   $query->sortOldest();
                   break;
           }
       } 
       else $query->sortCategory();

       return view('world.gears', [
           'gears' => $query->paginate(20)->appends($request->query()),
           'categories' => ['none' => 'Any Category'] + GearCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
       ]);
    }  
    
    /**
     * Shows an individual gear's page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGear($id)
    {
        $categories = GearCategory::orderBy('sort', 'DESC')->get();
        $gear = Gear::where('id', $id)->first();
        if(!$gear) abort(404);

        return view('world.gear_page', [
            'gear' => $gear,
            'imageUrl' => $gear->imageUrl,
            'name' => $gear->displayName,
            'description' => $gear->parsed_description,
            'categories' => $categories->keyBy('id'),
        ]);
    }

    /**
     * Shows the character classes page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterClasses(Request $request)
    {
        $query = CharacterClass::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.character_class', [
            'classes' => $query->orderBy('name', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the enchantment categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEnchantmentCategories(Request $request)
    {
        $query = EnchantmentCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.enchantment_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /** 
    * Shows the enchantments page.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Contracts\Support\Renderable
    */
   public function getEnchantments(Request $request)
   {
       $query = Enchantment::with('category');
       $data = $request->only(['enchantment_category_id', 'name', 'sort']);
       if(isset($data['enchantment_category_id']) && $data['enchantment_category_id'] != 'none') 
           $query->where('enchantment_category_id', $data['enchantment_category_id']);
       if(isset($data['name'])) 
           $query->where('name', 'LIKE', '%'.$data['name'].'%');

       if(isset($data['sort'])) 
       {
           switch($data['sort']) {
               case 'alpha':
                   $query->sortAlphabetical();
                   break;
               case 'alpha-reverse':
                   $query->sortAlphabetical(true);
                   break;
               case 'category':
                   $query->sortCategory();
                   break;
               case 'newest':
                   $query->sortNewest();
                   break;
               case 'oldest':
                   $query->sortOldest();
                   break;
           }
       } 
       else $query->sortCategory();

       return view('world.enchantments', [
           'enchantments' => $query->paginate(20)->appends($request->query()),
           'categories' => ['none' => 'Any Category'] + EnchantmentCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
       ]);
    }  

    /**
     * Shows an individual enchantment's page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEnchantment($id)
    {
        $categories = EnchantmentCategory::orderBy('sort', 'DESC')->get();
        $enchantment = Enchantment::where('id', $id)->first();
        if(!$enchantment) abort(404);

        return view('world.enchantment_page', [
            'enchantment' => $enchantment,
            'imageUrl' => $enchantment->imageUrl,
            'name' => $enchantment->displayName,
            'description' => $enchantment->parsed_description,
            'categories' => $categories->keyBy('id'),
        ]);
    }


}
