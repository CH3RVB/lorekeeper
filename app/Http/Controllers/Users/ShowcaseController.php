<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use Auth;
use Route;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseStock;
use App\Models\Item\Item;
use App\Services\InventoryManager;


use App\Services\ShowcaseService;

use App\Http\Controllers\Controller;
use App\Services\PetManager;
use App\Models\Pet\Pet;

class ShowcaseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User / Showcase Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of showcases and showcase stock.
    |
    */

    /**
     * Shows the showcase index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserIndex()
    {
        return view('home.showcases.my_showcases', [
            'showcases' => Showcase::where('user_id', Auth::user()->id)->orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create showcase page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShowcase()
    {
        return view('home.showcases.create_edit_showcase', [
            'showcase' => new Showcase
        ]);
    }

    /**
     * Shows the edit showcase page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShowcase($id)
    {
        $showcase = Showcase::find($id);
        if(!$showcase) abort(404);
        if($showcase->user_id != Auth::user()->id && !Auth::user()->hasPower('edit_inventories')) abort(404);
        return view('home.showcases.create_edit_showcase', [
            'showcase' => $showcase,
        ]);
    }

    /**
     * Creates or edits a showcase.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShowcaseService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShowcase(Request $request, ShowcaseService $service, $id = null)
    {
        $id ? $request->validate(Showcase::$updateRules) : $request->validate(Showcase::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_active'
        ]);
        if($id && $service->updateShowcase(Showcase::find($id), $data, Auth::user())) {
            flash('Showcase updated successfully.')->success();
        }
        else if (!$id && $showcase = $service->createShowcase($data, Auth::user())) {
            flash(ucfirst(__('showcase.showcase')).' created successfully.')->success();
            return redirect()->to(__('showcase.showcases').'/edit/'.$showcase->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the showcase deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShowcase($id)
    {
        $showcase = Showcase::find($id);
        return view('home.showcases._delete_showcase', [
            'showcase' => $showcase,
        ]);
    }

    /**
     * Deletes a showcase.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShowcaseService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShowcase(Request $request, ShowcaseService $service, $id)
    {
        if($id && $service->deleteShowcase(Showcase::find($id))) {
            flash(ucfirst(__('showcase.showcase')).' deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to(__('showcase.showcases'));
    }

    /**
     * Sorts showcases.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShowcaseService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShowcase(Request $request, ShowcaseService $service)
    {
        if($service->sortShowcase($request->get('sort'))) {
            flash(ucfirst(__('showcase.showcase')).' order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }


    /**
     * quickstock items
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postQuickstockStock(Request $request, ShowcaseService $service, $id)
    {
        if($service->quickstockStock($request->only(['stock_id','quantity','is_visible']), Showcase::where('id', $id)->first(), Auth::user())) {
            flash('Stock edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}