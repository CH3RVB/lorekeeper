<?php

namespace App\Http\Controllers\Admin\Claymores;

use Illuminate\Http\Request;

use App\Models\Character\CharacterClass;

use App\Services\Claymore\CharacterClassService;

use App\Http\Controllers\Controller;

class CharacterClassController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Character Class Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character class.
    |
    */

    /**
     * Shows the character class index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.claymores.classes.character_class', [
            'class' => CharacterClass::orderBy('name', 'DESC')->get()
        ]);
    }
    
    /**
     * Shows the create character class page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCharacterClass()
    {
        return view('admin.claymores.classes.create_edit_character_class', [
            'class' => new CharacterClass,
        ]);
    }
    
    /**
     * Shows the edit character class page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCharacterClass($id)
    {
        $class = CharacterClass::find($id);
        if(!$class) abort(404);
        return view('admin.claymores.classes.create_edit_character_class', [
            'class' => $class,
        ]);
    }

    /**
     * Creates or edits a character class.
     *
     * @param  \Illuminate\Http\Request               $request
     * @param  App\Services\CharacterClassService  $service
     * @param  int|null                               $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCharacterClass(Request $request, CharacterClassService $service, $id = null)
    {
        $id ? $request->validate(CharacterClass::$updateRules) : $request->validate(CharacterClass::$createRules);
        $data = $request->only([
            'code', 'name', 'description', 'image', 'remove_image', 'masterlist_sub_id'
        ]);
        if($id && $service->updateCharacterClass(CharacterClass::find($id), $data)) {
            flash('Class updated successfully.')->success();
        }
        else if (!$id && $class = $service->createCharacterClass($data)) {
            flash('Class created successfully.')->success();
            return redirect()->to('admin/character-classes/edit/'.$class->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the character class deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCharacterClass($id)
    {
        $class = CharacterClass::find($id);
        return view('admin.claymores.classes._delete_character_class', [
            'class' => $class,
        ]);
    }

    /**
     * Deletes a character class.
     *
     * @param  \Illuminate\Http\Request               $request
     * @param  App\Services\CharacterClassService  $service
     * @param  int                                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCharacterClass(Request $request, CharacterClassService $service, $id)
    {
        if($id && $service->deleteCharacterClass(CharacterClass::find($id))) {
            flash('Class deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/character-classes');
    }
}
