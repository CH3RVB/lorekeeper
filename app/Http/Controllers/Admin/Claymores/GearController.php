<?php

namespace App\Http\Controllers\Admin\Claymores;

use Illuminate\Http\Request;

use Auth;
use Config;
use Settings;

use App\Http\Controllers\Controller;

use App\Models\Claymore\GearCategory;
use App\Models\Claymore\Gear;

use App\Models\Character\CharacterClass;

use App\Services\Claymore\GearService;
use App\Models\Stat\Stat;
use App\Models\Currency\Currency;

class GearController extends Controller
{
    /**
     * Shows the gear index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGearIndex(Request $request)
    {
        $query = Gear::query();
        $data = $request->only(['gear_category_id', 'name']);
        if(isset($data['gear_category_id']) && $data['gear_category_id'] != 'none')
            $query->where('gear_category_id', $data['gear_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.claymores.gear.gears', [
            'gears' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + GearCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the create gear page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGear()
    {
        return view('admin.claymores.gear.create_edit_gear', [
            'gear' => new Gear,
            'gears' => ['none' => 'No parent'] + Gear::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No category'] + GearCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'currencies' => ['none' => 'No Parent ', 0 => 'Stat Points'] + Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the edit gear page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGear($id)
    {
        $gear = Gear::find($id);
        if(!$gear) abort(404);
        return view('admin.claymores.gear.create_edit_gear', [
            'gear' => $gear,
            'gears' => ['none' => 'No parent'] + Gear::orderBy('name', 'DESC')->where('id', '!=', $id)->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No category'] + GearCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'stats' => Stat::orderBy('name')->get(),
            'currencies' => ['none' => 'No Parent ', 0 => 'Stat Points'] + Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Creates or edits an gear.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\GearService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditGear(Request $request, GearService $service, $id = null)
    {
        $id ? $request->validate(Gear::$updateRules) : $request->validate(Gear::$createRules);
        $data = $request->only([
            'name', 'allow_transfer', 'gear_category_id', 'description', 'image', 'remove_image', 'currency_id', 'cost', 'parent_id', 'slots'
        ]);
        if($id && $service->updateGear(Gear::find($id), $data, Auth::user())) {
            flash('Gear updated successfully.')->success();
        }
        else if (!$id && $gear = $service->createGear($data, Auth::user())) {
            flash('Gear created successfully.')->success();
            return redirect()->to('admin/gear/edit/'.$gear->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the gear deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteGear($id)
    {
        $gear = Gear::find($id);
        return view('admin.claymores.gear._delete_gear', [
            'gear' => $gear,
        ]);
    }

    /**
     * Creates or edits an gear.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\GearService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGear(Request $request, GearService $service, $id)
    {
        if($id && $service->deleteGear(Gear::find($id))) {
            flash('Gear deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/gear');
    }

    public function postEditGearStats(Request $request, GearService $service, $id)
    {
        if ($id && $service->editStats($request->only(['stats']), $id)) {
            flash('Gear stats edited successfully.')->success();
            return redirect()->to('admin/gear/edit/'.$id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************

        GEAR CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the gear category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGearCategoryIndex()
    {
        return view('admin.claymores.gear.gear_categories', [
            'categories' => GearCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create gear category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGearCategory()
    {
        return view('admin.claymores.gear.create_edit_gear_category', [
            'category' => new GearCategory,
            'classes' => ['none' => 'No restriction'] + CharacterClass::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit gear category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGearCategory($id)
    {
        $category = GearCategory::find($id);
        if(!$category) abort(404);
        return view('admin.claymores.gear.create_edit_gear_category', [
            'category' => $category,
            'classes' => ['none' => 'No restriction'] + CharacterClass::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an gear category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\GearService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditGearCategory(Request $request, GearService $service, $id = null)
    {
        $id ? $request->validate(GearCategory::$updateRules) : $request->validate(GearCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'class_restriction'
        ]);
        if($id && $service->updateGearCategory(GearCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createGearCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/gear/gear-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the gear category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteGearCategory($id)
    {
        $category = GearCategory::find($id);
        return view('admin.claymores.gear._delete_gear_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an gear category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\GearService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGearCategory(Request $request, GearService $service, $id)
    {
        if($id && $service->deleteGearCategory(GearCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/gear/gear-categories');
    }

    /**
     * Sorts gear categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\GearService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortGearCategory(Request $request, GearService $service)
    {
        if($service->sortGearCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
