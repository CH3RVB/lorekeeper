<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Services\LimitManager;
use Illuminate\Http\Request;

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
    public function editLimit(Request $request, LimitManager $service, $model, $id)
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

        if ($id && $service->editLimits($object, $data)) {
            flash('Limits updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

}