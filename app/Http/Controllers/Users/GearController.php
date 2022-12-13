<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Models\User\User;
use App\Models\User\UserGear;
use App\Models\Claymore\Gear;
use App\Models\Claymore\Enchantment;
use App\Models\User\UserEnchantment;
use App\Models\Claymore\GearCategory;
use App\Models\Claymore\GearLog;
use App\Services\Claymore\GearManager;
use App\Models\Character\Character;
use App\Models\User\UserItem;
use App\Models\Item\ItemTag;
use App\Services\InventoryManager;
use App\Services\Item\GearSlotService;


use App\Http\Controllers\Controller;

class GearController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Gear Controller
    |--------------------------------------------------------------------------
    |
    | Handles gear management for the user.
    |
    */

    /**
     * Shows the user's gear page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $categories = GearCategory::orderBy('sort', 'DESC')->get();
        $gears = count($categories) ? Auth::user()->gears()->orderByRaw('FIELD(gear_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('name')->orderBy('updated_at')->get()->groupBy('gear_category_id') : Auth::user()->gears()->orderBy('name')->orderBy('updated_at')->get()->groupBy('gear_category_id');
        return view('home.gear', [
            'categories' => $categories->keyBy('id'),
            'gears' => $gears,
            'userOptions' => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Shows the gear stack modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStack(Request $request, $id)
    {
        $stack = UserGear::withTrashed()->where('id', $id)->with('gear')->first();
        $chara = Character::where('user_id', $stack->user_id)->pluck('slug', 'id');
        $enchantments = Enchantment::orderBy('name', 'DESC')->pluck('name', 'id')->toArray();
        $tags = ItemTag::where('tag', 'gear_slot')->where('is_active', 1)->pluck('item_id');
        $gear_slots = UserItem::where('user_id', $stack->user_id)->whereIn('item_id', $tags)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id');

        $readOnly = $request->get('read_only') ? : ((Auth::check() && $stack && !$stack->deleted_at && ($stack->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories'))) ? 0 : 1);

        return view('home._gear_stack', [
            'stack' => $stack,
            'chara' => $chara,
            'enchantments' => $enchantments,
            'user' => Auth::user(),
            'userOptions' => ['' => 'Select User'] + User::visible()->where('id', '!=', $stack ? $stack->user_id : 0)->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'readOnly' => $readOnly,
            'gear_slots' => $gear_slots
        ]);
    }
    
    /**
     * Transfers an gear stack to another user.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\GearManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, GearManager $service, $id)
    {
        if($service->transferStack(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), UserGear::where('id', $id)->first())) {
            flash('Gear transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Deletes an gear stack.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\GearManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, GearManager $service, $id)
    {
        if($service->deleteStack(Auth::user(), UserGear::where('id', $id)->first())) {
            flash('Gear deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Attaches an gear.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAttach(Request $request, GearManager $service, $id)
    {
        if($service->attachStack(UserGear::find($id), $request->get('id'))) {
            flash('Gear attached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Detaches an gear.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDetach(Request $request, GearManager $service, $id)
    {
        if($service->detachStack(UserGear::find($id))) {
            flash('Gear detached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the gear selection widget.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id)
    {
        return view('widgets._gear_select', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Upgrades
     */
    public function postUpgrade($id, GearManager $service)
    {
        $gear = UserGear::find($id);
        if(Auth::user()->isStaff && $gear->user_id != Auth::user()->id) $isStaff = true;
        else $isStaff = false;

        if($service->upgrade($gear, $isStaff)) {
            flash('Gear upgraded successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * adds slot
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSlot(Request $request, GearManager $service, $id)
    {
        $gear = UserGear::find($id);
        $tags = ItemTag::where('tag', 'gear_slot')->where('is_active', 1)->pluck('item_id');

        
        if($request->input('stack_id')) {
            $item = UserItem::find($request->input('stack_id'));
            $invman = new InventoryManager;
            if(!$invman->debitStack($gear->user, 'Used to add gear slot', ['data' => 'Used to add '.$gear->gear->name.' slot'], $item, 1)) {
                flash('Could not debit slot.')->error();
                return redirect()->back();
            }
        }
        if($service->editSlot($request->input('slots'), $gear)) {
            flash('Gear slot added successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
