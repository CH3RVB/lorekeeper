<?php
namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterTransfer;
use App\Models\Currency\Currency;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesBid;
use App\Models\Sales\SalesCharacter;
use App\Models\User\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class SalesService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Sales Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of Sales posts.
    |
    */

    /**
     * Creates a Sales post.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Sales
     */
    public function createSales($data, $user)
    {
        DB::beginTransaction();

        try {
            $data['parsed_text'] = parse($data['text']);
            $data['user_id']     = $user->id;
            if (! isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (! isset($data['is_open'])) {
                $data['is_open'] = 0;
            }

            if (! isset($data['limit_setting'])) {
                $data['limit_setting'] = 0;
            }

            $data['is_admin'] = 1;

            $sales = Sales::create($data);

            // The character identification comes in both the slug field and as character IDs
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }
            } else {
                $characters = [];
            }

            // Process entered character data
            if (isset($data['slug'])) {
                foreach ($data['slug'] as $key => $slug) {
                    $character = Character::myo(0)->where('slug', $slug)->first();
                    $this->processCharacters($sales, $data, $key, $character);
                }
            }

            if ($sales->is_visible) {
                $this->alertUsers();
            }

            return $this->commitReturn($sales);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a Sales post.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $sales
     *
     * @return bool|Sales
     */
    public function updateSales($sales, $data, $user)
    {
        DB::beginTransaction();

        try {
            $data['parsed_text'] = parse($data['text']);
            $data['user_id']     = $user->id;
            if (! isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (! isset($data['is_open'])) {
                $data['is_open'] = 0;
            }

            if (! isset($data['limit_setting'])) {
                $data['limit_setting'] = 0;
            }

            if (isset($data['bump']) && $data['is_visible'] == 1 && $data['bump'] == 1) {
                $this->alertUsers();
            }

            // The character identification comes in both the slug field and as character IDs
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }
            } else {
                $characters = [];
            }

            // sales characters need to retain their bids, so we update instead of deleting now unless we have to
            //this is so cursed and roundabout but mandatory.
            //i suffer for the sins of the lk community.
            if (isset($data['slug'])) {
                $exists = [];
                foreach ($data['slug'] as $key => $slug) {
                    $character = Character::myo(0)->where('slug', $slug)->first();
                    $c         = $sales->characters->where('character_id', $character->id)->first();
                    if ($c) {
                        $this->processCharacters($sales, $data, $key, $character, false, $c);
                        $exists[] = $character->id;
                    } else {
                        $this->processCharacters($sales, $data, $key, $character);
                        $exists[] = $character->id;
                    }

                }
                $deleted = $sales->characters->whereNotIn('character_id', $exists);
                if ($deleted) {
                    foreach ($deleted as $d) {
                        $d->delete();
                    }
                }
            } elseif (count($sales->characters)) {
                $sales->characters()->delete();
            }

            $sales->update($data);

            return $this->commitReturn($sales);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a Sales post.
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function deleteSales($sales)
    {
        DB::beginTransaction();

        try {

            $sales->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates queued Sales posts to be visible and alert users when
     * they should be posted.
     *
     * @return bool
     */
    public function updateQueue()
    {
        $count = Sales::shouldBeVisible()->count();
        if ($count) {
            DB::beginTransaction();

            try {
                Sales::shouldBeVisible()->update(['is_visible' => 1]);
                $this->alertUsers();

                return $this->commitReturn(true);
            } catch (\Exception $e) {
                $this->setError('error', $e->getMessage());
            }

            return $this->rollbackReturn(false);
        }
    }

    /**
     * Processes sales data entered for characters.
     *
     * @param App\Models\Sales\Sales $sales
     * @param array                  $data
     *
     * @return bool
     */
    private function processCharacters($sales, $data, $key, $character, $create = true, $c = null)
    {
        // Assemble data
        $charData[$key]         = [];
        $charData[$key]['type'] = $data['sale_type'][$key];

        if (isset($charData[$key]['currency_id'])) {

            $currency = Currency::find($charData[$key]['currency_id']);
            if (! $currency) {
                throw new \Exception("Invalid currency selected.");
            }
            //maybe include validation for a cost later, but some people may not know an exact price they want 100% of the time

        }
        $charData[$key]['currency_id']  = isset($data['currency_id'][$key]) ? $data['currency_id'][$key] : null;
        $charData[$key]['irl_currency'] = isset($data['irl_currency'][$key]) ? $data['irl_currency'][$key] : null;
        $charData[$key]['is_fto']       = isset($data['is_fto'][$key]) ? $data['is_fto'][$key] : null;

        if (isset($data['recipient_id'][$key])) {
            $recipient = User::find($data['recipient_id'][$key]);

            if (! $recipient) {
                throw new \Exception("Invalid recipient selected.");
            }
        }
        switch ($charData[$key]['type']) {
            case 'flatsale':
                $charData[$key]['price'] = $data['price'][$key];
                break;
            case 'auction':
                $charData[$key]['starting_bid']  = $data['starting_bid'][$key];
                $charData[$key]['min_increment'] = $data['min_increment'][$key];
                if (isset($data['autobuy'][$key])) {
                    $charData[$key]['autobuy'] = $data['autobuy'][$key];
                }
                if (isset($data['end_point'][$key])) {
                    $charData[$key]['end_point'] = $data['end_point'][$key];
                } else {
                    throw new \Exception("Must set an end point.");
                }
                if (isset($data['snipe_guard'][$key])) {
                    $charData[$key]['snipe_guard'] = $data['snipe_guard'][$key];
                }
                if (isset($data['snipe_period'][$key])) {
                    $charData[$key]['snipe_period'] = $data['snipe_period'][$key];
                }
                break;
            case 'ota':case 'xta':
                if (isset($data['autobuy'][$key])) {
                    $charData[$key]['autobuy'] = $data['autobuy'][$key];
                }
                if (isset($data['end_point'][$key])) {
                    $charData[$key]['end_point'] = $data['end_point'][$key];
                }
                if (isset($data['minimum'][$key])) {
                    $charData[$key]['minimum'] = $data['minimum'][$key];
                }
                break;
            case 'flaffle':
                $charData[$key]['price'] = $data['price'][$key];
                if (isset($data['end_point'][$key])) {
                    $charData[$key]['end_point'] = $data['end_point'][$key];
                }
                break;
            case 'pwyw':
                if (isset($data['minimum'][$key])) {
                    $charData[$key]['minimum'] = $data['minimum'][$key];
                }
                break;
            case 'raffle':
                if (isset($data['end_point'][$key])) {
                    $charData[$key]['end_point'] = $data['end_point'][$key];
                }
                break;
        }

        // Validate data
        $validator = Validator::make($charData[$key], SalesCharacter::$rules);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $finalData = [
            'character_id' => $character->id,
            'sales_id'     => $sales->id,
            'type'         => $charData[$key]['type'],
            'data'         => $charData[$key],
            'description'  => $data['description'][$key] ?? null,
            'link'         => $data['link'][$key] ?? null,
            'is_open'      => $data['character_is_open'][$character->slug] ?? ($data['new_entry'][$key] ? 1 : 0),
            'recipient_id' => isset($data['recipient_id'][$key]) ? Auth::user()->id : null,
        ];

        if ($create == true) {
            // Record data/attach the character to the sales post
            SalesCharacter::create($finalData);
        } else {
            $c->update($finalData);
        }
    }

    /**
     * Updates the unread Sales flag for all users so that
     * the new Sales notification is displayed.
     *
     * @return bool
     */
    private function alertUsers()
    {
        User::query()->update(['is_sales_unread' => 1]);

        return true;
    }

    /**
     * Creates a Sales post.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Sales
     */
    public function rollSales($sales, $user)
    {
        DB::beginTransaction();

        try {

            //then roll all raffles and flaffles
            foreach ($sales->characters as $c) {
                if (! $this->rollCharacter($c)) {
                    throw new \Exception("Failed to roll sales winners.");
                }
            }

            $sales->is_open = 0;
            $sales->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a Sales post.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Sales
     */
    public function rollCharacter($c)
    {
        DB::beginTransaction();

        try {
            // roll winners
            if ($winner = $this->rollWinner($c)) {
                // mark raffle as finished
                $c->update(['is_rolled' => 1, 'is_open' => 0]);

                // updates the raffle group if necessary
                if ($c->sales->limit_setting == 2 && ! $this->afterRoll($winner, $c)) {
                    throw new \Exception("Failed to roll winners.");
                }
            } else {
                throw new \Exception("Failed to roll winners.");
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Rolls the winners of a sales.
     *
     * @param  \App\Models\Sales\Sales $sales
     * @return array
     */
    private function rollWinner($c)
    {
        $ticketPool  = $c->bids;
        $ticketCount = $ticketPool->count();
        if ($ticketCount == 0) {
            return;
        }

        $num    = mt_rand(0, $ticketCount - 1);
        $winner = $ticketPool[$num];

        //if we're rerolling, there can only be one winner, unset it
        $fwin = $c->bids->where('status', 'Accepted')->first();
        if ($fwin) {
            $fwin->update(['status' => 'Pending']);
        }
        // save ticket position as ($i + 1)
        $winner->update(['status' => 'Accepted']);
        $c->update(['winner_id' => $winner->user_id]);

        return $winner->user_id;
    }

    /**
     * Rolls the winners of a sales.
     *
     * @param  array                          $winners
     * @param  \App\Models\Sales\SalesGroup $salesGroup
     * @param  \App\Models\Sales\Sales      $sales
     * @return bool
     */
    private function afterRoll($winner, $c)
    {
        // remove any tickets from winners in sales in the group that aren't completed
        foreach ($c->sales->characters->whereIn('type', ['flaffle', 'raffle'])->whereIn('is_rolled', 0)->where('id', '!=', $c->id) as $r) {
            $r->bids()->where(function ($query) use ($winner) {
                $query->where('user_id', $winner);
            })->delete();
        }
        return true;
    }

    /**********************************************************************************************

        BUY

    **********************************************************************************************/

    /**
     * Create a bid on a sale
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function makeBid($character, $user, $data)
    {
        DB::beginTransaction();

        try {
            $charData        = $character->data;
            $sales           = $character->sales;
            $currencyManager = new CurrencyManager;
            $isonsite        = isset($charData['currency_id']);

            $payment = $data['price'] ?? null;
            //flatsales are very simple lol.
            if ($character->type == 'flatsale' || $character->type == 'flaffle') {
                $payment = $charData['price'];
            } elseif ($character->type == 'auction') {

                //snipe guard
                if (isset($charData['snipe_period']) && $charData['snipe_guard']) {
                    //if the bid is made after the snipe period
                    if (Carbon::now() >= $character->auctionEnd->subMinutes($charData['snipe_period'])) {
                        $snipe = true;
                    }
                }

                //AB supersedes all
                if (isset($data['autobuy']) && $charData['autobuy']) {
                    if (! isset($data['confirm_ab'])) {
                        throw new \Exception("Please confirm that you wish to AB.");
                    }
                    $payment = $charData['autobuy'];
                    $ab      = true;
                } //if no bids, it's the SB
                elseif (! $character->lastBid) {
                    if ($data['price'] < $charData['starting_bid']) {
                        throw new \Exception("You must meet or exceed the starting bid.");
                    }
                } else {
                    if ($character->lastBid->user_id == Auth::user()->id) {
                        throw new \Exception("You are already the latest bid.");
                    }
                    //otherwise do auction stuff
                    //check last bid
                    if ($data['price'] < $character->minBid) {
                        throw new \Exception("You must meet or exceed the minimum increment.");
                    }

                    //store last bid info before we make a new bid
                    $lastBid = true;
                    $last    = $character->lastBid;

                    //i need to doublecheck if auctions debit the full amount each time. ideally remove the user's last bid from their current bid
                }

            }

            if ($isonsite && isset($payment)) {
                $currency = Currency::find($charData['currency_id']);
                if (! $currency) {
                    throw new \Exception("Invalid currency selected.");
                }

                if (! $currencyManager->debitCurrency($user, null, null, null, $currency, $payment)) {
                    throw new \Exception("Invalid currency/quantity selected.");
                }

                $price    = $payment;
                $currType = $currency->id;

            } elseif (! $isonsite) {
                //otherwise prime for the paypal
                if (! isset($data['email'])) {
                    throw new \Exception("You must set a paypal email for payment.");
                }
                $currType = 'paypal';
                $price    = $payment;

            }

            if (isset($data['comments']) && $data['comments']) {
                $data['parsed_comments'] = parse($data['comments']);
            } else {
                $data['parsed_comments'] = null;
            }

            $bid = SalesBid::create([
                'sales_id'           => $sales->id,
                'paypal_email'       => $data['email'] ?? null,
                'user_id'            => $user->id,
                'sales_character_id' => $character->id,
                'data'               => [
                    'bid'             => $price, //gonna log the payment here just in case even if it's flatsale.
                    'currency'        => $currType,
                    'snipe'           => $snipe ?? null,
                    'autobuy'         => $ab ?? null,
                    'comments'        => $data['comments'] ?? null,
                    'parsed_comments' => $data['parsed_comments'],
                ],
            ]);

            if (! $bid) {
                throw new \Exception("Failed to create offer.");
            }

            //create the notif
            if ($character->type == 'auction' && isset($lastBid)) {
                Notifications::create('SALES_OUTBID', $last->user, [
                    'bid_id'         => $bid->id,
                    'sender_url'     => $user->url,
                    'sender_name'    => $user->name,
                    'character_name' => $character->character->name,
                    'character_url'  => $character->url,
                ]);
            } else {
                Notifications::create('SALES_OFFER_PLACED', $bid->recipient, [
                    'bid_id'      => $bid->id,
                    'sender_url'  => $user->url,
                    'sender_name' => $user->name,
                ]);
            }

            return $this->commitReturn($bid);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function acceptBid($bid, $seller)
    {
        DB::beginTransaction();

        try {
            //if its irl put it approved to be confirmed and such
            if ($bid->paymentType == 'irl') {
                $bid->update(['status' => 'Accepted']);
            } else {

                //else handle the char
                $queueClosed = Settings::get('open_transfers_queue');

                //if the q is open or it's a site sale then just do stuff
                if ($bid->sales->is_admin || ! $queueClosed) {
                    if (! $this->handleCurrency($bid, $bid->recipient, $bid->user)) {
                        throw new \Exception("Failed to accept " . $bid->type(true) . ".");
                    }
                    if (! $this->makeTransfer($bid, $bid->sales)) {
                        throw new \Exception("Failed to accept " . $bid->type(true) . ".");
                    }
                } else {
                    CharacterTransfer::create([
                        'user_reason'  => 'Character sale  (<a href="' . $bid->sales->url . '">#' . $bid->sales->id . '</a>)',
                        'character_id' => $bid->salesCharacter->character->id,
                        'sender_id'    => $bid->sales->user_id,
                        'recipient_id' => $bid->user_id,
                        'status'       => 'Accepted', //the user already placed their bid so auto approve
                        'is_approved'  => ! $queueClosed,
                        'sales_bid_id' => $bid->id, //flag to tie everything back after approval so mods don't have to hunt around
                    ]);

                    //approval pending
                    $bid->update(['status' => 'Approval']);
                }

            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Feature bid
     * @param mixed $sales
     *
     * @return bool
     */
    public function handleCurrency($bid, $seller, $buyer)
    {

        DB::beginTransaction();

        try {

            $sales = $bid->sales;

            $currencyManager = new CurrencyManager;
            //return all attachments for other users
            $otherbids = $sales->bids()->where('user_id', '!=', $buyer->id)->get();
            if ($otherbids->count()) {
                foreach ($otherbids as $ob) {
                    if ($bid->paymentType == 'onsite' && isset($ob->data['bid'])) {
                        $currency = Currency::find($ob->data['currency']);
                        if (! $currency) {
                            throw new \Exception("Invalid currency selected.");
                        }

                        if (! $currencyManager->creditCurrency(null, $ob->user, null, null, $currency, $ob->data['bid'])) {
                            foreach ($currencyManager->errors()->getMessages()['error'] as $error) {
                                flash($error)->error();
                            }
                        }
                    }
                }
            }
            //transfer the currency

            if ($bid->paymentType == 'onsite') {
                foreach (['buyer', 'seller'] as $type) {
                    $recipientType = ($type == 'buyer') ? $buyer : $seller;
                    $senderType    = ($type == 'buyer') ? $buyer : $seller;
                    $currency      = Currency::find($bid->data['currency']);
                    if (! $currency) {
                        throw new \Exception("Invalid currency selected.");
                    }

                    if (! $currencyManager->creditCurrency($senderType, $recipientType, 'Character Sale', 'Received in a character sale (<a href="' . $sales->url . '">#' . $sales->id . '</a>)', $currency, $bid->data['bid'])) {
                        foreach ($currencyManager->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * Feature bid
     * @param mixed $sales
     *
     * @return bool
     */
    public function featureBid($bid)
    {

        DB::beginTransaction();

        try {

            if ($bid->is_featured == 0) {
                $bid->update(['is_featured' => 1]);
            } else {
                $bid->update(['is_featured' => 0]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function requestBid($bid, $user, $data)
    {
        DB::beginTransaction();

        try {
            $buyer = $bid->user;

            $character = $bid->salesCharacter;
            $ogData    = $bid->data;
            $seller    = $bid->recipient;
            if (! $data['email']) {
                throw new \Exception('You must enter a paypal address.');
            }

            $bid->update(['status' => 'Requested', 'data' => $ogData + ['seller_paypal_email' => $data['email'] ?? null]]);

            Notifications::create('SALES_REQUESTED', $buyer, [
                'bid_id'         => $bid->id,
                'sender_url'     => $seller->url,
                'sender_name'    => $seller->name,
                'character_name' => $character->character->name,
                'character_url'  => $character->url,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function payBid($bid, $buyer)
    {
        DB::beginTransaction();

        try {

            $character = $bid->salesCharacter;
            $seller    = $bid->recipient;
            $bid->update(['status' => 'Paid']);

            Notifications::create('SALES_PAID', $buyer, [
                'bid_id'         => $bid->id,
                'sender_url'     => $seller->url,
                'sender_name'    => $seller->name,
                'character_name' => $character->character->name,
                'character_url'  => $character->url,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function cancelBid($bid, $user)
    {
        DB::beginTransaction();

        try {

            $bid->update(['status' => 'Pending']);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function completeBid($bid, $user)
    {
        DB::beginTransaction();

        try {

            $isonsite = isset($charData['currency_id']);

            //now put it in the queue and such

            //else handle the char
            $queueClosed = Settings::get('open_transfers_queue');
            //if the q is open or it's a site sale then just do stuff
            if ($bid->sales->is_admin || ! $queueClosed) {
                //return any currency and also credit to the seller
                if ($isonsite) {
                    if (! $this->handleCurrency($bid, $bid->recipient, $bid->user)) {
                        throw new \Exception("Failed to handle currency.");
                    }
                }
                if (! $this->makeTransfer($bid, $bid->sales)) {
                    throw new \Exception("Failed to accept " . $bid->type(true) . ".");
                }
            } else {
                CharacterTransfer::create([
                    'user_reason'  => 'Character sale  (<a href="' . $bid->sales->url . '">#' . $bid->sales->id . '</a>)',
                    'character_id' => $bid->salesCharacter->character->id,
                    'sender_id'    => $bid->sales->user_id,
                    'recipient_id' => $bid->user_id,
                    'status'       => 'Accepted', //the user already placed their bid so auto approve
                    'is_approved'  => ! $queueClosed,
                    'sales_bid_id' => $bid->id, //flag to tie everything back after approval so mods don't have to hunt around
                ]);

                //approval pending
                $bid->update(['status' => 'Approval']);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function makeTransfer($bid, $sales)
    {
        DB::beginTransaction();

        try {
            $characterManager = new CharacterManager;
            //move char
            $characterManager->moveCharacter($bid->salesCharacter->character, $bid->user, 'Character sale (<a href="' . $sales->url . '">#' . $sales->id . '</a>)', Settings::get('transfer_cooldown'), 'Character Sale');

            $bid->salesCharacter->character->update(['sales_id' => null]);
            $bid->salesCharacter->update(['is_open' => false]);
            $bid->update(['status' => 'Complete']);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function completeApprovedBid($bid, $user)
    {
        DB::beginTransaction();

        try {

            //finally,  check for onsite currency, debit and grant it
            $isonsite = isset($charData['currency_id']);

            //return any currency and also credit to the seller
            if ($isonsite) {
                if (! $this->handleCurrency($bid, $bid->recipient, $bid->user)) {
                    throw new \Exception("Failed to handle currency.");
                }
            }

            //and finalize the status
            $bid->update(['status' => 'Complete']);
            $bid->salesCharacter->update(['is_open' => false]);

            //unset sale id so character can be transferred again
            $bid->salesCharacter->character->update(['sales_id' => null]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a sale offer
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function rejectBid($bid, $user)
    {
        DB::beginTransaction();

        try {

            $currencyManager = new CurrencyManager;
            //return any currency the bidder placed
            if ($bid->paymentType == 'onsite' && isset($ob->data['bid'])) {
                $currency = Currency::find($ob->data['currency']);
                if (! $currency) {
                    throw new \Exception("Invalid currency selected.");
                }

                if (! $currencyManager->creditCurrency(null, $ob->user, null, null, $currency, $ob->data['bid'])) {
                    foreach ($currencyManager->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                }
            }

            $bid->update(['status' => 'Rejected']);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
