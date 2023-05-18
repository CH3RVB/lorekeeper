<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Pet\PetCategory;
use App\Models\Pet\Pet;

use App\Services\PetService;

use App\Http\Controllers\Controller;

class PetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Pet Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of pet categories and pets.
    |
    */

    /**********************************************************************************************
    
        PET CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the pet category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.pets.pet_categories', [
            'categories' => PetCategory::orderBy('sort', 'DESC')->get()
        ]);
    }
    
    /**
     * Shows the create pet category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreatePetCategory()
    {
        return view('admin.pets.create_edit_pet_category', [
            'category' => new PetCategory
        ]);
    }
    
    /**
     * Shows the edit pet category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPetCategory($id)
    {
        $category = PetCategory::find($id);
        if(!$category) abort(404);
        return view('admin.pets.create_edit_pet_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits an pet category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PetService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPetCategory(Request $request, PetService $service, $id = null)
    {
        $id ? $request->validate(PetCategory::$updateRules) : $request->validate(PetCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image'
        ]);
        if($id && $service->updatePetCategory(PetCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createPetCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/data/pet-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the pet category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePetCategory($id)
    {
        $category = PetCategory::find($id);
        return view('admin.pets._delete_pet_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an pet category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PetService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePetCategory(Request $request, PetService $service, $id)
    {
        if($id && $service->deletePetCategory(PetCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/pet-categories');
    }

    /**
     * Sorts pet categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PetService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortPetCategory(Request $request, PetService $service)
    {
        if($service->sortPetCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************
    
        PETS

    **********************************************************************************************/

    /**
     * Shows the pet index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPetIndex(Request $request)
    {
        $query = Pet::query();
        $data = $request->only(['pet_category_id', 'name']);
        if(isset($data['pet_category_id']) && $data['pet_category_id'] != 'none') 
            $query->where('pet_category_id', $data['pet_category_id']);
        if(isset($data['name'])) 
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.pets.pets', [
            'pets' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + PetCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }
    
    /**
     * Shows the create pet page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreatePet()
    {
        return view('admin.pets.create_edit_pet', [
            'pet' => new Pet,
            'categories' => ['none' => 'No category'] + PetCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }
    
    /**
     * Shows the edit pet page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPet($id)
    {
        $pet = Pet::find($id);
        if(!$pet) abort(404);
        return view('admin.pets.create_edit_pet', [
            'pet' => $pet,
            'categories' => ['none' => 'No category'] + PetCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Creates or edits an pet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PetService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPet(Request $request, PetService $service, $id = null)
    {
        $id ? $request->validate(Pet::$updateRules) : $request->validate(Pet::$createRules);
        $data = $request->only([
            'name', 'allow_transfer', 'pet_category_id', 'description', 'image', 'remove_image'
        ]);
        if($id && $service->updatePet(Pet::find($id), $data, Auth::user())) {
            flash('Pet updated successfully.')->success();
        }
        else if (!$id && $pet = $service->createPet($data, Auth::user())) {
            flash('Pet created successfully.')->success();
            return redirect()->to('admin/data/pets/edit/'.$pet->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the pet deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePet($id)
    {
        $pet = Pet::find($id);
        return view('admin.pets._delete_pet', [
            'pet' => $pet,
        ]);
    }

    /**
     * Creates or edits an pet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PetService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePet(Request $request, PetService $service, $id)
    {
        if($id && $service->deletePet(Pet::find($id))) {
            flash('Pet deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/pets');
    }

    /**
     * Edits pet variants
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PetService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditVariants(Request $request, PetService $service, $id)
    {
        if($id && $service->editVariants($request->only(['variant_names', 'variant_images']), Pet::find($id))) {
            flash('Variants editted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
