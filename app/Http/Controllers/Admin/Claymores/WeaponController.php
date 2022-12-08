<?php

namespace App\Http\Controllers\Admin\Claymores;

use Illuminate\Http\Request;

use Auth;
use Config;
use Settings;

use App\Http\Controllers\Controller;

use App\Models\Claymore\WeaponCategory;
use App\Models\Claymore\Weapon;

use App\Models\Character\CharacterClass;

use App\Services\Claymore\WeaponService;
use App\Models\Stat\Stat;
use App\Models\Currency\Currency;

class WeaponController extends Controller
{
    /**
     * Shows the weapon index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWeaponIndex(Request $request)
    {
        $query = Weapon::query();
        $data = $request->only(['weapon_category_id', 'name']);
        if(isset($data['weapon_category_id']) && $data['weapon_category_id'] != 'none')
            $query->where('weapon_category_id', $data['weapon_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.claymores.weapon.weapons', [
            'weapons' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + WeaponCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the create weapon page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateWeapon()
    {
        return view('admin.claymores.weapon.create_edit_weapon', [
            'weapon' => new Weapon,
            'weapons' => ['none' => 'No Parent '] + Weapon::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No category'] + WeaponCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'currencies' => ['none' => 'No Parent ', 0 => 'Stat Points'] + Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit weapon page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditWeapon($id)
    {
        $weapon = Weapon::find($id);
        if(!$weapon) abort(404);
        return view('admin.claymores.weapon.create_edit_weapon', [
            'weapon' => $weapon,
            'weapons' => ['none' => 'No Parent '] + Weapon::orderBy('name', 'DESC')->where('id', '!=', $id)->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No category'] + WeaponCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'currencies' => ['none' => 'No Parent ', 0 => 'Stat Points'] + Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
            'stats' => Stat::orderBy('name')->get(),
        ]);
    }

    /**
     * Creates or edits an weapon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeaponService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditWeapon(Request $request, WeaponService $service, $id = null)
    {
        $id ? $request->validate(Weapon::$updateRules) : $request->validate(Weapon::$createRules);
        $data = $request->only([
            'name', 'allow_transfer', 'weapon_category_id', 'description', 'image', 'remove_image', 'currency_id', 'cost', 'parent_id'
        ]);
        if($id && $service->updateWeapon(Weapon::find($id), $data, Auth::user())) {
            flash('Weapon updated successfully.')->success();
        }
        else if (!$id && $weapon = $service->createWeapon($data, Auth::user())) {
            flash('Weapon created successfully.')->success();
            return redirect()->to('admin/weapon/edit/'.$weapon->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the weapon deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteWeapon($id)
    {
        $weapon = Weapon::find($id);
        return view('admin.claymores.weapon._delete_weapon', [
            'weapon' => $weapon,
        ]);
    }

    /**
     * Creates or edits an weapon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeaponService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteWeapon(Request $request, WeaponService $service, $id)
    {
        if($id && $service->deleteWeapon(Weapon::find($id))) {
            flash('Weapon deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/weapon');
    }

    public function postEditWeaponStats(Request $request, WeaponService $service, $id)
    {
        if ($id && $service->editStats($request->only(['stats']), $id)) {
            flash('Weapon stats edited successfully.')->success();
            return redirect()->to('admin/weapon/edit/'.$id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************

        WEAPON CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the weapon category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWeaponCategoryIndex()
    {
        return view('admin.claymores.weapon.weapon_categories', [
            'categories' => WeaponCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create weapon category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateWeaponCategory()
    {
        return view('admin.claymores.weapon.create_edit_weapon_category', [
            'category' => new WeaponCategory,
            'classes' => ['none' => 'No restriction'] + CharacterClass::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit weapon category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditWeaponCategory($id)
    {
        $category = WeaponCategory::find($id);
        if(!$category) abort(404);
        return view('admin.claymores.weapon.create_edit_weapon_category', [
            'category' => $category,
            'classes' => ['none' => 'No restriction'] + CharacterClass::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an weapon category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeaponService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditWeaponCategory(Request $request, WeaponService $service, $id = null)
    {
        $id ? $request->validate(WeaponCategory::$updateRules) : $request->validate(WeaponCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'class_restriction',
        ]);
        if($id && $service->updateWeaponCategory(WeaponCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createWeaponCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/weapon/weapon-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the weapon category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteWeaponCategory($id)
    {
        $category = WeaponCategory::find($id);
        return view('admin.claymores.weapon._delete_weapon_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an weapon category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeaponService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteWeaponCategory(Request $request, WeaponService $service, $id)
    {
        if($id && $service->deleteWeaponCategory(WeaponCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/weapon/weapon-categories');
    }

    /**
     * Sorts weapon categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeaponService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortWeaponCategory(Request $request, WeaponService $service)
    {
        if($service->sortWeaponCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
