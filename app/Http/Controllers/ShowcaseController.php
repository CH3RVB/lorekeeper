<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\ShowcaseManager;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseStock;
use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Item\ItemCategory;
use App\Models\User\UserItem;
use App\Models\Pet\PetCategory;

class ShowcaseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Showcase Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the showcase index, showcases and viewing showcases.
    |
    */

    /**
     * Shows the user list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $query = Showcase::visible();
        $sort = $request->only(['sort']);

        if($request->get('name')) $query->where(function($query) use ($request) {
            $query->where('showcases.name', 'LIKE', '%' . $request->get('name') . '%');
        }); 

        switch(isset($sort['sort']) ? $sort['sort'] : null) {
            default:
                $query->orderBy('name', 'DESC');
                break;
            case 'alpha':
                $query->orderBy('name');
                break;
            case 'alpha-reverse':
                $query->orderBy('name', 'DESC');
                break;
            case 'newest':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        return view('home.showcases.index_showcase', [
            'showcases' => $query->paginate(30)->appends($request->query()), 
        ]);
    }

    /**
     * Shows a showcase.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShowcase($id)
    {
        $categories = ItemCategory::orderBy('sort', 'DESC')->get();
        $petCategories = PetCategory::orderBy('sort', 'DESC')->get();
        $showcase = Showcase::where('id', $id)->where('is_active', 1)->first();
        if(!$showcase) abort(404);
        $items = count($categories) ? $showcase->displayStock()->orderByRaw('FIELD(item_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('name')->get()->groupBy('item_category_id') : $showcase->displayStock()->orderBy('name')->get()->groupBy('item_category_id');
        $pets = count($petCategories) ? $showcase->displayPetStock()->orderByRaw('FIELD(pet_category_id,'.implode(',', $petCategories->pluck('id')->toArray()).')')->orderBy('name')->get()->groupBy('pet_category_id') : $showcase->displayPetStock()->orderBy('name')->get()->groupBy('pet_category_id');
        return view('home.showcases.showcase', [
            'showcase' => $showcase,
            'categories' => $categories->keyBy('id'),
            'items' => $items,
            'pets' => $pets,
            'petCategories' => $petCategories->keyBy('id'),
        ]);
    }
}