<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Models\User\User;
use App\Models\User\UserEnchantment;
use App\Models\Claymore\Enchantment;
use App\Models\Claymore\EnchantmentCategory;
use App\Models\Claymore\EnchantmentLog;
use App\Services\Claymore\EnchantmentManager;
use App\Models\Claymore\Gear;
use App\Models\User\UserGear;
use App\Models\Claymore\Weapon;
use App\Models\User\UserWeapon;
use App\Models\Item\ItemTag;
use App\Models\User\UserItem;
use App\Services\InventoryManager;

use App\Http\Controllers\Controller;

class EnchantmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Enchantment Controller
    |--------------------------------------------------------------------------
    |
    | Handles enchantment management for the user.
    |
    */

    /**
     * Shows the user's enchantment page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $categories = EnchantmentCategory::orderBy('sort', 'DESC')->get();
        $enchantments = count($categories) ? Auth::user()->enchantments()->orderByRaw('FIELD(enchantment_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('name')->orderBy('updated_at')->get()->groupBy('enchantment_category_id') : Auth::user()->enchantments()->orderBy('name')->orderBy('updated_at')->get()->groupBy('enchantment_category_id');
        return view('home.enchantment', [
            'categories' => $categories->keyBy('id'),
            'enchantments' => $enchantments,
            'userOptions' => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Shows the enchantment stack modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStack(Request $request, $id)
    {
        $stack = UserEnchantment::withTrashed()->where('id', $id)->with('enchantment')->first();
        $gear = UserGear::where('user_id', $stack->user_id)->get()->pluck('gear.name', 'id');
        $weapon = UserWeapon::where('user_id', $stack->user_id)->get()->pluck('weapon.name', 'id');
        $unench = ItemTag::where('tag', 'unenchant')->where('is_active', 1)->pluck('item_id');
        $unenchants = UserItem::where('user_id', $stack->user_id)->whereIn('item_id', $unench)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id');

        $readOnly = $request->get('read_only') ? : ((Auth::check() && $stack && !$stack->deleted_at && ($stack->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories'))) ? 0 : 1);

        return view('home._enchantment_stack', [
            'stack' => $stack,
            'gear' => $gear,
            'weapon' => $weapon,
            'user' => Auth::user(),
            'userOptions' => ['' => 'Select User'] + User::visible()->where('id', '!=', $stack ? $stack->user_id : 0)->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'gearOptions' => ['' => 'Select Gear'] + UserGear::where('user_id', $stack->user_id)->get()->pluck('nameWithSlot', 'id')->toArray(),
            'weaponOptions' => ['' => 'Select Weapon'] + UserWeapon::where('user_id', $stack->user_id)->get()->pluck('nameWithSlot', 'id')->toArray(),
            'readOnly' => $readOnly,
            'unenchants' => $unenchants
        ]);
    }
    
    /**
     * Transfers an enchantment stack to another user.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\EnchantmentManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, EnchantmentManager $service, $id)
    {
        if($service->transferStack(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), UserEnchantment::where('id', $id)->first())) {
            flash('Enchantment transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Deletes an enchantment stack.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\EnchantmentManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, EnchantmentManager $service, $id)
    {
        if($service->deleteStack(Auth::user(), UserEnchantment::where('id', $id)->first())) {
            flash('Enchantment deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Attaches an enchantment.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\GearManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAttach(Request $request, EnchantmentManager $service, $id)
    {
        if($service->attachEnchantStack(UserEnchantment::find($id), $request->get('id'), $request->get('type'))) {
            flash('Enchantment attached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Detaches an enchantment.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\GearManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDetach(Request $request, EnchantmentManager $service, $id)
    {
        if($service->detachEnchantStack(UserEnchantment::find($id))) {
            flash('Enchantment detached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

        /**
     * Attaches an enchantment.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\WeaponManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postWeaponAttach(Request $request, EnchantmentManager $service, $id)
    {
        if($service->attachEnchantWeaponStack(UserEnchantment::find($id), $request->get('id'))) {
            flash('Enchantment attached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the enchantment selection widget.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id)
    {
        return view('widgets._enchantment_select', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Upgrades
     */
    public function postUpgrade($id, EnchantmentManager $service)
    {
        $enchantment = UserEnchantment::find($id);
        if(Auth::user()->isStaff && $enchantment->user_id != Auth::user()->id) $isStaff = true;
        else $isStaff = false;

        if($service->upgrade($enchantment, $isStaff)) {
            flash('Enchantment upgraded successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * remove enchantment 
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUnenchant(Request $request, EnchantmentManager $service, $id)
    {
        $enchantment = UserEnchantment::find($id);
        $tags = ItemTag::where('tag', 'unenchant')->where('is_active', 1)->pluck('item_id');

        
        //debit the item if not staff
        if (!Auth::user()->isStaff) {
            if($request->input('stack_id')) {
                $item = UserItem::find($request->input('stack_id'));
                $invman = new InventoryManager;
                if(!$invman->debitStack($enchantment->user, 'Used to remove enchantment', ['data' => 'Used to remove enchantment from claymore.'], $item, 1)) {
                    flash('Could not debit unenchantment.')->error();
                    return redirect()->back();
                }
            }
        }
        if($service->detachEnchantStack($enchantment)) {
            flash('Enchantment detached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
