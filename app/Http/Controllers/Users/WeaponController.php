<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Models\User\User;
use App\Models\User\UserWeapon;
use App\Models\Claymore\Weapon;
use App\Models\Claymore\WeaponCategory;
use App\Models\Claymore\WeaponLog;
use App\Services\Claymore\WeaponManager;
use App\Models\Character\Character;

use App\Http\Controllers\Controller;

class WeaponController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Weapon Controller
    |--------------------------------------------------------------------------
    |
    | Handles weapon management for the user.
    |
    */

    /**
     * Shows the user's weapon page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $categories = WeaponCategory::orderBy('sort', 'DESC')->get();
        $weapons = count($categories) ? Auth::user()->weapons()->orderByRaw('FIELD(weapon_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('name')->orderBy('updated_at')->get()->groupBy('weapon_category_id') : Auth::user()->weapons()->orderBy('name')->orderBy('updated_at')->get()->groupBy('weapon_category_id');
        return view('home.weapon', [
            'categories' => $categories->keyBy('id'),
            'weapons' => $weapons,
            'userOptions' => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Shows the weapon stack modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStack(Request $request, $id)
    {
        $stack = UserWeapon::withTrashed()->where('id', $id)->with('weapon')->first();
        $chara = Character::where('user_id', $stack->user_id)->pluck('slug', 'id');

        $readOnly = $request->get('read_only') ? : ((Auth::check() && $stack && !$stack->deleted_at && ($stack->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories'))) ? 0 : 1);

        return view('home._weapon_stack', [
            'stack' => $stack,
            'chara' => $chara,
            'user' => Auth::user(),
            'userOptions' => ['' => 'Select User'] + User::visible()->where('id', '!=', $stack ? $stack->user_id : 0)->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'readOnly' => $readOnly
        ]);
    }
    
    /**
     * Transfers an weapon stack to another user.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\WeaponManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, WeaponManager $service, $id)
    {
        if($service->transferStack(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), UserWeapon::where('id', $id)->first())) {
            flash('Weapon transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Deletes an weapon stack.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\WeaponManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, WeaponManager $service, $id)
    {
        if($service->deleteStack(Auth::user(), UserWeapon::where('id', $id)->first())) {
            flash('Weapon deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Attaches an weapon.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAttach(Request $request, WeaponManager $service, $id)
    {
        if($service->attachStack(UserWeapon::find($id), $request->get('id'))) {
            flash('Weapon attached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Detaches an weapon.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDetach(Request $request, WeaponManager $service, $id)
    {
        if($service->detachStack(UserWeapon::find($id))) {
            flash('Weapon detached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the weapon selection widget.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id)
    {
        return view('widgets._weapon_select', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Upgrades
     */
    public function postUpgrade($id, WeaponManager $service)
    {
        $weapon = UserWeapon::find($id);
        if(Auth::user()->isStaff && $weapon->user_id != Auth::user()->id) $isStaff = true;
        else $isStaff = false;

        if($service->upgrade($weapon, $isStaff)) {
            flash('Weapon upgraded successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Unique image
     */
    public function postImage($id, Request $request, WeaponManager $service)
    {
        $weapon = UserWeapon::find($id);
        $data = $request->only(['image']);

        if(!Auth::user()->isStaff) abort(404);

        if($service->image($weapon, $data)) {
            flash('Weapon image updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
