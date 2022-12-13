<?php

namespace App\Http\Controllers\Admin\Claymores;

use Illuminate\Http\Request;

use Auth;
use Config;
use Settings;

use App\Http\Controllers\Controller;

use App\Models\Claymore\EnchantmentCategory;
use App\Models\Claymore\Enchantment;

use App\Models\Character\CharacterClass;

use App\Services\Claymore\EnchantmentService;
use App\Models\Stat\Stat;
use App\Models\Currency\Currency;

class EnchantmentController extends Controller
{
    /**
     * Shows the enchantment index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEnchantmentIndex(Request $request)
    {
        $query = Enchantment::query();
        $data = $request->only(['enchantment_category_id', 'name']);
        if(isset($data['enchantment_category_id']) && $data['enchantment_category_id'] != 'none')
            $query->where('enchantment_category_id', $data['enchantment_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.claymores.enchantment.enchantments', [
            'enchantments' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + EnchantmentCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the create enchantment page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEnchantment()
    {
        return view('admin.claymores.enchantment.create_edit_enchantment', [
            'enchantment' => new Enchantment,
            'enchantments' => ['0' => 'No parent'] + Enchantment::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No category'] + EnchantmentCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'currencies' => ['none' => 'No Parent ', 0 => 'Stat Points'] + Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit enchantment page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditEnchantment($id)
    {
        $enchantment = Enchantment::find($id);
        if(!$enchantment) abort(404);
        return view('admin.claymores.enchantment.create_edit_enchantment', [
            'enchantment' => $enchantment,
            'enchantments' => ['0' => 'No parent'] + Enchantment::orderBy('name', 'DESC')->where('id', '!=', $id)->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No category'] + EnchantmentCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'stats' => Stat::orderBy('name')->get(),
            'currencies' => ['none' => 'No Parent ', 0 => 'Stat Points'] + Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an enchantment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\EnchantmentService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditEnchantment(Request $request, EnchantmentService $service, $id = null)
    {
        $id ? $request->validate(Enchantment::$updateRules) : $request->validate(Enchantment::$createRules);
        $data = $request->only([
            'name', 'enchantment_category_id', 'description', 'image', 'remove_image', 'currency_id', 'cost','parent_id', 'allow_transfer'
        ]);
        if($id && $service->updateEnchantment(Enchantment::find($id), $data, Auth::user())) {
            flash('Enchantment updated successfully.')->success();
        }
        else if (!$id && $enchantment = $service->createEnchantment($data, Auth::user())) {
            flash('Enchantment created successfully.')->success();
            return redirect()->to('admin/enchantment/edit/'.$enchantment->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the enchantment deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteEnchantment($id)
    {
        $enchantment = Enchantment::find($id);
        return view('admin.claymores.enchantment._delete_enchantment', [
            'enchantment' => $enchantment,
        ]);
    }

    /**
     * Creates or edits an enchantment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\EnchantmentService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteEnchantment(Request $request, EnchantmentService $service, $id)
    {
        if($id && $service->deleteEnchantment(Enchantment::find($id))) {
            flash('Enchantment deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/enchantment');
    }

    public function postEditEnchantmentStats(Request $request, EnchantmentService $service, $id)
    {
        if ($id && $service->editStats($request->only(['stats']), $id)) {
            flash('Enchantment stats edited successfully.')->success();
            return redirect()->to('admin/enchantment/edit/'.$id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************

        Enchantment CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the enchantment category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEnchantmentCategoryIndex()
    {
        return view('admin.claymores.enchantment.enchantment_categories', [
            'categories' => EnchantmentCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create enchantment category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEnchantmentCategory()
    {
        return view('admin.claymores.enchantment.create_edit_enchantment_category', [
            'category' => new EnchantmentCategory,
            'classes' => ['none' => 'No restriction'] + CharacterClass::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit enchantment category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditEnchantmentCategory($id)
    {
        $category = EnchantmentCategory::find($id);
        if(!$category) abort(404);
        return view('admin.claymores.enchantment.create_edit_enchantment_category', [
            'category' => $category,
            'classes' => ['none' => 'No restriction'] + CharacterClass::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an enchantment category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\EnchantmentService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditEnchantmentCategory(Request $request, EnchantmentService $service, $id = null)
    {
        $id ? $request->validate(EnchantmentCategory::$updateRules) : $request->validate(EnchantmentCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'class_restriction'
        ]);
        if($id && $service->updateEnchantmentCategory(EnchantmentCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createEnchantmentCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/enchantment/enchantment-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the enchantment category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteEnchantmentCategory($id)
    {
        $category = EnchantmentCategory::find($id);
        return view('admin.claymores.enchantment._delete_enchantment_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an enchantment category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\EnchantmentService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteEnchantmentCategory(Request $request, EnchantmentService $service, $id)
    {
        if($id && $service->deleteEnchantmentCategory(EnchantmentCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/enchantment/enchantment-categories');
    }

    /**
     * Sorts enchantment categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\EnchantmentService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortEnchantmentCategory(Request $request, EnchantmentService $service)
    {
        if($service->sortEnchantmentCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
