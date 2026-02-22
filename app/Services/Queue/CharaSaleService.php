<?php
namespace App\Services\Queue;

use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Queue\Queue;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesCharacter;
use App\Models\User\User;
use App\Services\Service;
use App\Services\UserSalesService;
use Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class CharaSaleService extends Service
{

    /**
     * Retrieves any data that should be used in the queue type editing form on the admin side
     *
     * @return array
     */
    public function getEditData()
    {

        return [

        ];
    }

    /**
     * Retrieves any data that should be used in the queue type on the user side
     *
     * @return array
     */
    public function getActData($queue)
    {
        return [
            'charSelect'  => Auth::user()->characters->pluck('fullName', 'slug'),
            'userOptions' => ['' => 'Select User'] + User::visible()->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'currencies'  => Currency::where('allow_user_to_user', 1)->where('is_user_owned', 1)->orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getData($data)
    {
        return $data;
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  object  $queue
     * @param  array   $data
     * @return bool
     */
    public function updateData($queue, $data)
    {

        return [
        ];
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getSubmissionData($queue, $submission, $data)
    {

        return $data;
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getCharacterData($queue, $submission, $character, $data)
    {

    //displayType
        switch ($data['type']) {
            case 'flatsale':
                $data['displayType'] = 'Flatsale';
                break;
            case 'ota':
                $data['displayType'] = 'OTA';
                break;
            case 'xta':
                $data['displayType'] = 'XTA';
                break;
            case 'raffle':
                $data['displayType'] = 'Raffle';
                break;
            case 'pwyw':
                $data['displayType'] = 'PWYW';
                break;
        }

        //price display
        if ($data['type'] == 'raffle') {
            $data['displayPrice'] = 'Free';
        }
        $symbol = isset($data['data']['irl_currency']) ? $data['data']['irl_currency'] : config('lorekeeper.settings.currency_symbol');

 switch ($data['type']) {
            case 'flatsale':case 'flaffle':
                $data['displayPrice'] = 'Price: ' . $symbol . $data['data']['price'];
                break;
            case 'auction':
                $data['displayPrice'] = 'Starting Bid: ' . $symbol . $data['data']['starting_bid'] . '<br/>' .
                    'Minimum Increment: ' . $symbol . $data['data']['min_increment'] .
                    (isset($data['data']['autobuy']) ? '<br/>Autobuy: ' . $symbol . $data['data']['autobuy'] : '');
                break;
            case 'ota':case 'xta':
                $data['displayPrice'] = (isset($data['data']['autobuy']) ? 'Autobuy: ' . $symbol . $data['data']['autobuy'] . '<br/>' : '') .
                    (isset($data['data']['minimum']) ? 'Minimum: ' . $symbol . $data['data']['minimum'] . '<br/>' : '');
                break;
            case 'pwyw':
                $data['displayPrice'] = isset($data['data']['minimum']) ? 'Minimum: ' . $symbol . $data['data']['minimum'] . '<br/>' : '';
                break;
        }

        return $data;
    }

    /**
     * Handle any validation on-submit to the queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function submit($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {

            //any data handled here should only be that which is required by this particular queue type, as the rest is already handled by the queue service itself

            //let's start by validating the input we have from the user :tm:

            if (! isset($data['slug'])) {
                throw new \Exception("You must select at least one character.");
            }

            foreach ($data['slug'] as $key => $slug) {
                $queue->generalService->checkCharacterOwnership($slug, $user);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle any validation on-submit to the queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function processSubmit($queue, $data, $user, $submission)
    {
        // Validate data
        $validator = Validator::make($data, Sales::$createRules);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return [
            'title'       => $data['title'],
            'text'        => $data['text'],
            'parsed_text' => parse($data['text']),
        ];
    }

    /**
     * Process and get any other data to be saved in the submission itself.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function finalizeCharacterAttachments($queue, $data, $submission, $user)
    {

        $character = Character::myo(0)->where('id', $data['character_id'])->first();

        if ($character->user_id != $user->id && ! $user->hasPower('edit_inventories')) {
            throw new \Exception("You do not own this character.");
        }
        $key = $data['character_id'];

        // Assemble data
        $charData[$key]         = [];
        $charData[$key]['type'] = $data['sale_type'][$key];
        if (isset($charData[$key]['currency_id'][$key])) {

            $currency = Currency::find($charData[$key]['currency_id'][$key]);
            if (! $currency) {
                throw new \Exception("Invalid currency selected.");
            }
            //maybe include validation for a cost later, but some people may not know an exact price they want 100% of the time

        }
        $charData[$key]['currency_id']  = isset($data['currency_id'][$key]) ? $data['currency_id'][$key] : null;
        $charData[$key]['irl_currency'] = isset($data['irl_currency'][$key]) ? $data['irl_currency'][$key] : null;

        if (! $character->isAvailable) {
            throw new \Exception("This character is currently in an open trade or transfer. Please cancel the trade or transfer before trying to sell it.");
        }

        if ($character->transferrable_at && $character->transferrable_at->isFuture()) {
            throw new \Exception("One or more of the selected characters is still on transfer cooldown and cannot be transferred.");
        }

        if (isset($charData[$key]['currency_id']) && ! $character->is_sellable || isset($charData[$key]['irl_currency']) && ! $character->is_sellable) {
            throw new \Exception("One or more selected characters cannot be sold.");
        }

        //limiting the sales options for now-- from what i remember users don't really host auctions themselves...

        //may add flaffle back, not sure if it's useful on the user end
        switch ($charData[$key]['type']) {
            case 'flatsale':
                $charData[$key]['price'] = isset($data['price'][$key]) ? $data['price'][$key] : null;
                break;
            case 'ota':
                if (isset($data['autobuy'][$key])) {
                    $charData[$key]['autobuy'] = $data['autobuy'][$key];
                }
                if (isset($data['minimum'][$key])) {
                    $charData[$key]['minimum'] = $data['minimum'][$key];
                }
                if (isset($data['end_point'][$key])) {
                    $charData[$key]['end_point'] = $data['end_point'][$key];
                }
                break;
            case 'xta':
                if (isset($data['autobuy'][$key])) {
                    $charData[$key]['autobuy'] = $data['autobuy'][$key];
                }
                if (isset($data['minimum'][$key])) {
                    $charData[$key]['minimum'] = $data['minimum'][$key];
                }
                if (isset($data['end_point'][$key])) {
                    $charData[$key]['end_point'] = $data['end_point'][$key];
                }
                break;
            case 'pwyw':
                if (isset($data['minimum'][$key])) {
                    $charData[$key]['minimum'] = $data['minimum'][$key];
                }
                break;

        }
        // Validate data
        $validator = Validator::make($charData[$key], SalesCharacter::$rules);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
        // Record data/attach the character to the sales post
        return [
            'type'         => $charData[$key]['type'],
            'data'         => $charData[$key],
            'description'  => $data['description'][$key] ?? null,
            'recipient_id' => isset($data['recipient_id'][$key]) ? $user->id : null,
        ];

    }

    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function approve($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {

            //create the sale
            $saledata = [
                'user_id'          => $user->id,
                'text'             => $data['text'],
                'parsed_text'      => parse($data['text']),
                'title'            => $data['title'],
                'description'      => $data['description'],
                'is_visible'       => 1,
                'is_open'          => 1,
                'post_at'          => null,
                'comments_open_at' => null,
                'is_admin'         => 0,
            ];

            // Validate data
            $validator = Validator::make($saledata, Sales::$createRules);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $sales = Sales::create($saledata);

            if (! $sales) {
                throw new \Exception("Failed to create sales post.");
            }

            if ($submission->characters->count()) {
                foreach ($submission->characters as $character) {

                    $cdata = $character->data;
                    //create the sale
                    $chardata = [
                        'character_id' => $character->character_id,
                        'sales_id'     => $sales->id,
                        'type'         => $cdata['type'],
                        'data'         => $cdata['data'],
                        'description'  => $cdata['description'] ?? null,
                        'link'         => null,
                        'is_open'      => 1,
                        'recipient_id' => isset($cdata['recipient_id']) ? $user->id : null,
                    ];

                    // Validate data
                    $charvalidator = Validator::make($chardata, SalesCharacter::$rules);
                    if ($charvalidator->fails()) {
                        throw new \Exception($charvalidator->errors()->first());
                    }

                    $char = SalesCharacter::create($chardata);

                    if (! $char) {
                        throw new \Exception("Failed to create sales character.");
                    }
                    unset($char);
                    unset($chardata);
                    unset($cdata);

                    $character->character->update(['sales_id' => $sales->id]);
                }
            }

            //throw a ping out
            $service = new UserSalesService;
            if (! $service->alertUsers()) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



}
