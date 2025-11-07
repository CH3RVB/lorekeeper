<?php
namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Item\ItemCategory;
use App\Models\Milestone;
use App\Services\MilestoneService;
use Auth;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Milestone Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of milestones.
    |
    */

    /**********************************************************************************************

        MILESTONES
    **********************************************************************************************/

    /**
     * Shows the milestone index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMilestoneIndex(Request $request)
    {
        $query = Milestone::query();
        $data  = $request->only(['milestone', 'category_id', 'subcategory_id', 'visibility', 'is_active', 'milestone_type', 'sort']);

        if (isset($data['category_id'])) {
            if ($data['category_id'] == 'withoutOption') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $data['category_id']);
            }
        }
        /*
        if (isset($data['subcategory_id'])) {
            if ($data['subcategory_id'] == 'withoutOption') {
                $query->whereNull('subcategory_id');
            } else {
                $query->where('subcategory_id', $data['subcategory_id']);
            }
        }
            */

        if (isset($data['milestone'])) {
            $query->where('milestone', 'LIKE', '%' . $data['milestone'] . '%');
        }

        if (isset($data['visibility'])) {
            if ($data['visibility'] == 'visibleOnly') {
                $query->where('is_visible', '=', 1);
            } else {
                $query->where('is_visible', '=', 0);
            }
        }
        if (isset($data['is_active'])) {
            if ($data['is_active'] == 'activeOnly') {
                $query->where('is_active', '=', 1);
            } else {
                $query->where('is_active', '=', 0);
            }
        }

        if (isset($data['milestone_type'])) {
            $query->where('milestone_type', $data['milestone_type']);
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory($data['milestone_type'] ?? null);
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        } else {
            $query->sortOldest();
        }

        $mss    = config('lorekeeper.milestones');
        $result = [];
        foreach ($mss as $ms => $msData) {
            $result[$ms] = $msData['name'];
        }

        return view('admin.milestones.milestones', [
            'milestones' => $query->paginate(20)->appends($request->query()),
            'categories' => ['withoutOption' => 'Without Category'] + ItemCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            //  'subcategories' => ['none' => 'Any Subcategory'] + ItemSubcategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'milestone_types'      => $result,
        ]);
    }

    /**
     * Shows the create milestone page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateMilestone()
    {

        $mss    = config('lorekeeper.milestones');
        $result = [];
        foreach ($mss as $ms => $msData) {
            $result[$ms] = $msData['name'];
        }

        return view('admin.milestones.create_edit_milestone', [
            'milestone'  => new Milestone,
            'categories' => [null => 'No category'] + ItemCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
           'milestone_types'      => $result,
        ]);
    }

    /**
     * Shows the edit milestone page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditMilestone($id)
    {
        $milestone = Milestone::find($id);
        if (! $milestone) {
            abort(404);
        }

         // get base modal from type using asset helper
        $type = $milestone->milestone_type;
        $model = getAssetModelString(strtolower($type));

        // check if categories exist for this model ($model.'Category')
        $categoryClass = $model.'Category';
        if (class_exists($categoryClass)) {
            $categories            = [null => 'No category'] + $categoryClass::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray();
        }else{
            $categories = [];
        }


        $mss    = config('lorekeeper.milestones');
        $result = [];
        foreach ($mss as $ms => $msData) {
            $result[$ms] = $msData['name'];
        }

        return view('admin.milestones.create_edit_milestone', [
            'milestone'  => $milestone,
            'categories' => $categories,
            'milestone_types'      => $result,
        ]);
    }

    /**
     * Creates or edits an milestone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\MilestoneService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditMilestone(Request $request, MilestoneService $service, $id = null)
    {
        $id ? $request->validate(Milestone::$updateRules) : $request->validate(Milestone::$createRules);
        $data = $request->only([
            'milestone', 'description', 'image', 'remove_image', 'is_visible', 'is_active', 'category_id', 'subcategory_id', 'summary', 'milestone_type',
        ]);
        if ($id && $service->updateMilestone(Milestone::find($id), $data, Auth::user())) {
            flash('Milestone updated successfully.')->success();
        } else if (! $id && $milestone = $service->createMilestone($data, Auth::user())) {
            flash('Milestone created successfully.')->success();
            return redirect()->to('admin/data/milestones/edit/' . $milestone->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the milestone deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteMilestone($id)
    {
        $milestone = Milestone::find($id);
        return view('admin.milestones._delete_milestone', [
            'milestone' => $milestone,
        ]);
    }

    /**
     * Creates or edits an milestone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\MilestoneService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteMilestone(Request $request, MilestoneService $service, $id)
    {
        if ($id && $service->deleteMilestone(Milestone::find($id))) {
            flash('Milestone deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->to('admin/data/milestones');
    }
}
