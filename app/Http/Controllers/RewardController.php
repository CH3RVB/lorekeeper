<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RewardController extends Controller {
    /**
     * Gets reward type dropdown select based on input.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postRewardTypes(Request $request) {
        $data = $request->only('recipient', 'prefix', 'type', 'showData');

        return view('widgets._loot_reward_types', [
            'prefix'      => $data['prefix'],
            'type'        => $data['type'],
            'rewardTypes' => getRewardTypes($data['showData'], $data['recipient']),
        ]);
    }

    /**
     * Gets limit type dropdown select based on input.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postLimitTypes(Request $request) {
        $data = $request->only('recipient', 'prefix', 'type');

        return view('widgets._limit_types', [
            'prefix'      => $data['prefix'],
            'type'        => $data['type'],
            'limitTypes'  => getLimitTypes($data['recipient']),
        ]);
    }
}
