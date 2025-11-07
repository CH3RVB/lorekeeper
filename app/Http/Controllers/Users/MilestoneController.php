<?php
namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Milestone;
use App\Models\User\User;
use App\Models\User\UserCharacterLog;
use App\Models\User\UserItem;
use App\Services\MilestoneManager;
use App\Services\MilestoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Milestone Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing milestones, as well as their usage.
    |
    */

    /**
     * Shows the user's trades.
     *
     * @param  string  $type
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $user = Auth::user();
        //genuinely so haunted to make different types possible. i owe newt for the original code here.

        // get all types of milestone
        $milestone_types = Milestone::visible($user)->pluck('milestone_type')->unique();
        $milestones      = [];
        $count           = [];
        foreach ($milestone_types as $type) {
            // get the model for the milestone type (item, pet, etc)
            $ogType = $type;
            $type   = strtolower($type);
            $conf   = config('lorekeeper.milestones.' . $ogType);
            if (getAssetModelString($type)) {
                $model = getAssetModelString($type);
            } elseif (! getAssetModelString($type) && $conf && isset($conf['path'])) {
                //attempt to get from path
                $model = $conf['path'];
            }
            // get the category of the milestone
            if (! class_exists($model . 'Category')) {
                $milestone           = Milestone::whereNotIn('id', $user->milestones()->pluck('milestone_id')->unique())->visible($user)->where('milestone_type', $ogType)->orderBy('id')->get()->groupBy('category_id');
                $milestones[$ogType] = $milestone;
                continue; // If the category model doesn't exist, skip it
            }
            $milestone_category = ($model . 'Category')::orderBy('sort', 'DESC')->get();
            // order the milestones
            $milestone = count($milestone_category) ? Milestone::whereNotIn('id', $user->milestones()->pluck('milestone_id')->unique())->visible($user)->where('milestone_type', $ogType)
                ->orderByRaw('FIELD(category_id,' . implode(',', $milestone_category->pluck('id')->toArray()) . ')')
                ->orderBy('id')->get()->groupBy('category_id')
                : Milestone::whereNotIn('id', $user->milestones()->pluck('milestone_id')->unique())->visible($user)->where('milestone_type', $ogType)->orderBy('id')->get()->groupBy('category_id');

            // make it so key "" appears last
            $milestone = $milestone->sortBy(function ($item, $key) {
                return $key == '' ? 1 : 0;
            });

            $milestones[$ogType] = $milestone;

            $owned = (new MilestoneService)->getOwned($ogType, $user);

            $count[$ogType] = $owned;
        }
        return view('home.milestones.index', [
            'incompleted' => $milestones,
            'iC'          => $count,
        ]);
    }

/**
 * Completes a milestone
 *
 * @param  integer  $id
 * @return \Illuminate\Contracts\Support\Renderable
 */
    public function postCompleteMilestone(Request $request, MilestoneManager $service, $id)
    {
        $milestone = Milestone::visible(Auth::user())->find($id);
        if (! $milestone) {
            abort(404);
        }

        if ($service->completeMilestone($milestone, Auth::user())) {
            flash('Milestone completed successfully. Congratulations!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

}
