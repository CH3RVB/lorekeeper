<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Services\RewardManager;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Reward Maker Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of rewards
    |
     */

    /**
     * Edit reward
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editReward(Request $request, RewardManager $service, $model, $id)
    {
        $decodedmodel = urldecode(base64_decode($model));
        //check model + id combo exists
        $object = $decodedmodel::find($id);
        if (!$object) {
            throw new \Exception('Invalid object.');
        }

        $data = $request->only([
            'rewardable_type', 'rewardable_id', 'reward_quantity','recipient_type'
        ]);

        if ($id && $service->editRewards($object, $data)) {
            flash('Rewards updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

}
