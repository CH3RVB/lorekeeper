<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\ObjectLimit;
use App\Models\Submission\Submission;
use App\Models\User\User;
use App\Models\User\UserCurrency;
use App\Models\User\UserItem;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LimitController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Limit Maker Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of limits
    |
     */

    /**
     * Edit limit
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editLimit(Request $request, $model, $id)
    {
        $decodedmodel = urldecode(base64_decode($model));
        //check model + id combo exists
        $object = $decodedmodel::find($id);
        if (!$object) {
            throw new \Exception('Invalid object.');
        }

        $data = $request->only([
            'limit_id', 'limit_type', 'quantity',
        ]);

        DB::beginTransaction();
        try {

            // We're going to remove all limits and reattach them with the updated data

            $object->objectLimits()->delete();

            if (isset($data['limit_id'])) {
                foreach ($data['limit_id'] as $key => $id) {
                    ObjectLimit::create([
                        'object_id' => $object->id,
                        'object_type' => class_basename($object),
                        'limit_type' => $data['limit_type'][$key],
                        'limit_id' => $id,
                        'quantity' => $data['quantity'][$key],
                    ]);
                }
            }

            DB::commit();

            flash('Limits edited successfully.')->success();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            flash($e->getMessage())->error();
            return redirect()->back();
        }
    }

    private function innerNull($value)
    {
        return array_values(array_filter($value));
    }

    /**
     * Approves a submission.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function checkLimits($object, $user)
    {
        DB::beginTransaction();

        try {
            if (!$object) {
                throw new \Exception("Invalid object.");
            }

            if (!$user) {
                throw new \Exception("Invalid user.");
            }

            if (!Auth::check()) {
                flash('You must be logged in.')->error();
                return redirect()->back();
            }

            foreach ($object->objectLimits as $limit) {

                $limitType = $limit->limit_type;
                $check = null;
                switch ($limitType) {
                    case 'Item':
                        $check = UserItem::where('item_id', $limit->limit_id)
                            ->where('user_id', $user->id)
                            ->where('count', '>=', $limit->quantity)
                            ->first();
                        break;
                    case 'Currency':
                        $check = UserCurrency::where('currency_id', $limit->limit_id)
                            ->where('user_id', $user->id)
                            ->where('count', '>=', $limit->quantity)
                            ->first();
                        break;
                }

                if (!$check) {
                    flash('You require ' . $limit->limit->name . ' x' . $limit->quantity . ' to complete this action.')->error();

                    return redirect()->back();
                }

            }

            DB::commit();

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            flash($e->getMessage())->error();
            return redirect()->back();
        }
    }

}
