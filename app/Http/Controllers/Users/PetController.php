<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Models\User\User;
use App\Models\User\UserPet;
use App\Models\Pet\Pet;
use App\Models\Pet\PetCategory;
use App\Models\Pet\PetLog;
use App\Models\Item\ItemTag;
use App\Models\User\UserItem;
use App\Services\PetManager;
use App\Services\InventoryManager;

use App\Models\Character\Character;

use App\Http\Controllers\Controller;

class PetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Pet Controller
    |--------------------------------------------------------------------------
    |
    | Handles pet management for the user.
    |
    */

    /**
     * Shows the user's pet page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $categories = PetCategory::orderBy('sort', 'DESC')->get();
        $pets = count($categories) ? Auth::user()->pets()->orderByRaw('FIELD(pet_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('name')->orderBy('updated_at')->get()->groupBy('pet_category_id') : Auth::user()->pets()->orderBy('name')->orderBy('updated_at')->get()->groupBy('pet_category_id');
        return view('home.pet', [
            'categories' => $categories->keyBy('id'),
            'pets' => $pets,
            'userOptions' => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Shows the pet stack modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStack(Request $request, $id)
    {
        $stack = UserPet::withTrashed()->where('id', $id)->with('pet')->first();
        $chara = Character::where('user_id', $stack->user_id)->pluck('slug', 'id');

        $readOnly = $request->get('read_only') ? : ((Auth::check() && $stack && !$stack->deleted_at && ($stack->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories'))) ? 0 : 1);

        $tags = ItemTag::where('tag', 'splice')->where('is_active', 1)->pluck('item_id');
        $splices = UserItem::where('user_id', $stack->user_id)->whereIn('item_id', $tags)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id');
        return view('home._pet_stack', [
            'stack' => $stack,
            'chara' => $chara,
            'user' => Auth::user(),
            'userOptions' => ['' => 'Select User'] + User::visible()->where('id', '!=', $stack ? $stack->user_id : 0)->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'readOnly' => $readOnly,
            'splices' => $splices
        ]);
    }

    /**
     * Transfers an pet stack to another user.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\PetManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, PetManager $service, $id)
    {
        if($service->transferStack(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), UserPet::where('id', $id)->first())) {
            flash('Pet transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Deletes an pet stack.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\PetManager  $service
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, PetManager $service, $id)
    {
        if($service->deleteStack(Auth::user(), UserPet::where('id', $id)->first())) {
            flash('Pet deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Names an pet.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postName(Request $request, PetManager $service, $id)
    {
        if($service->nameStack(UserPet::find($id), $request->get('name'))) {
            flash('Pet named successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Attaches an pet.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAttach(Request $request, PetManager $service, $id)
    {
        if($service->attachStack(UserPet::find($id), $request->get('id'))) {
            flash('Pet attached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Detaches an pet.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDetach(Request $request, PetManager $service, $id)
    {
        if($service->detachStack(UserPet::find($id))) {
            flash('Pet detached successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Changes variant
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postVariant(Request $request, PetManager $service, $id)
    {
        $pet = UserPet::find($id);
        if($request->input('stack_id')) {
            $item = UserItem::find($request->input('stack_id'));
            $invman = new InventoryManager;
            if(!$invman->debitStack($pet->user, 'Used to change pet variant', ['data' => 'Used to change '.$pet->pet->name.' variant'], $item, 1)) {
                flash('Could not debit splice.')->error();
                return redirect()->back();
            }
        }
        if($service->editVariant($request->input('variant_id'), $pet)) {
            flash('Pet variant changed successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the pet selection widget.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id)
    {
        return view('widgets._pet_select', [
            'user' => Auth::user(),
        ]);
    }


    /**
     * Shows a pet's drops page.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPetDrops($id)
    {
        $pet = UserPet::findOrFail($id);
        $user = $pet->user;

        if(!$pet->pet->hasDrops || (!$pet->drops->dropData->isActive && (!Auth::check() || !Auth::user()->hasPower('manage_inventory')))) abort(404);
        return view('user.pet_drops', [
            'pet' => $pet,
            'drops' => $pet->drops,
            'user' => $user
        ]);
    }

    /**
     * Claims pet drops.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @param  string                         $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postClaimPetDrops(Request $request, InventoryManager $service, $id)
    {
        $pet = UserPet::findOrFail($id);
        $user = $pet->user;

        if(!Auth::check()) abort(404);
        if($pet->user_id != Auth::user()->id) abort(404);
        $drops = $pet->drops;
        if(!$drops) abort(404);

        if($service->claimPetDrops($pet, $pet->user, $pet->drops)) {
            flash('Drops claimed successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}
