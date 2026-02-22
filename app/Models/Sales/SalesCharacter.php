<?php
namespace App\Models\Sales;

use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\User\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SalesCharacter extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_id', 'character_id', 'description', 'type', 'data', 'link', 'is_open', 'recipient_id', 'winner_id', 'is_rolled',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_characters';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = [
        'type'          => 'required',
        'link'          => 'nullable|url',

        // Flatsale
        'price'         => 'required_if:sale_type,flat',

        // Auction/XTA
        'starting_bid'  => 'required_if:type,auction',
        'min_increment' => 'required_if:type,auction',
        'end_point'     => 'exclude_unless:type,auction,xta,ota,raffle|max:255|required_if:sale_type,raffle,auction',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the sale this is attached to.
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    /**
     * Get the character being attached to the sale.
     */
    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id')->withTrashed();
    }

    /**
     * Get the user who owns the character.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the user who owns the character.
     */
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Get the character's valid bids
     */
    public function bids()
    {
        return $this->hasMany(SalesBid::class, 'sales_character_id')->whereNull('deleted_at')->where('status', '!=', 'Rejected');
    }

    /**
     * Get ALL bids, regardless of validity
     */
    public function allBids()
    {
        return $this->hasMany(SalesBid::class, 'sales_character_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return string
     */
    public function getDisplayTypeAttribute()
    {
        switch ($this->attributes['type']) {
            case 'flatsale':
                return 'Flatsale';
                break;
            case 'auction':
                return 'Auction';
                break;
            case 'ota':
                return 'OTA';
                break;
            case 'xta':
                return 'XTA';
                break;
            case 'flaffle':
                return 'Flatsale Raffle';
                break;
            case 'raffle':
                return 'Raffle';
                break;
            case 'pwyw':
                return 'PWYW';
                break;
        }
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return string
     */
    public function getTypeLinkAttribute()
    {
        switch ($this->attributes['type']) {
            case 'flatsale':case 'pwyw':
                return 'Claim';
                break;
            case 'auction':
                return 'Bid';
                break;
            case 'ota':
                return 'Offer';
                break;
            case 'flaffle':case 'raffle':case 'xta':
                return 'Enter';
                break;
        }
    }

    /**
     * Get formatted pricing information.
     *
     * @return string
     */
    public function getPriceAttribute()
    {
        if ($this->type == 'raffle') {
            return 'Free';
        }

        //this is... messy and probably not ideal...
        switch ($this->type) {
            case 'flatsale':case 'flaffle':
                return $this->costFormat($this->data['price']);
                break;
            case 'auction':
                return 'Starting Bid: ' . $this->costFormat($this->data['starting_bid']) . '<br/>' .
                'Minimum Increment: ' . $this->costFormat($this->data['min_increment']) .
                    (isset($this->data['autobuy']) ? '<br/>Autobuy: ' . $this->costFormat($this->data['autobuy']) : '');
                break;
            case 'ota':case 'xta':
                return (isset($this->data['autobuy']) ? 'Autobuy: ' . $this->costFormat($this->data['autobuy']) . '<br/>' : '') .
                    (isset($this->data['minimum']) ? 'Minimum: ' . $this->costFormat($this->data['minimum']) . '<br/>' : '');
                break;
            case 'pwyw':
                return isset($this->data['minimum']) ? 'Minimum: ' . $this->costFormat($this->data['minimum']) . '<br/>' : '';
                break;
        }
    }

    /**
     * Get the first image for the associated character.
     *
     * @return App\Models\Character\CharacterImage
     */
    public function getImageAttribute()
    {
        return CharacterImage::where('is_visible', 1)->where('character_id', $this->character_id)->orderBy('created_at')->first();
    }

    /**
     * Nicely combine currency symbols and prices so we don't have to do this every time when getting the price.
     *
     * @return string
     */
    public function costFormat($value)
    {
        if ($this->saleCurrency) {
            return $this->saleCurrency->display($value);
        }
        $symbol = isset($this->data['irl_currency']) ? $this->data['irl_currency'] : config('lorekeeper.settings.currency_symbol');
        return $symbol . ' ' . $value;
    }

    /**
     * Get the onsite currency for the sale.
     *
     * @return string
     */
    public function getSaleCurrencyAttribute()
    {
        if (! isset($this->data['currency_id'])) {
            return null;
        }
        $currency = Currency::find($this->data['currency_id']);
        if (! $currency) {
            return null;
        }
        return $currency;
    }

    /**
     * Get if a user can place a bid/offer
     *
     * @return App\Models\Character\CharacterImage
     */
    public function canBid($msg = false)
    {
        if (! Auth::check()) {
            if ($msg) {
                return 'You must be logged in.';
            }

            return false;
        }

        //cannot place offers on a closed chara or sale
        if (! $this->isActive) {
            if ($msg) {
                return 'The sale is closed.';
            }

            return false;
        }

        //cannot enter fto sale if not a fto
        if (isset($this->data['is_fto']) && ! Auth::user()->settings->is_fto) {
            if ($msg) {
                return 'You are not a FTO.';
            }

            return false;
        }

        //if there's a restriction
        if ($this->sales->limit_setting == 1) {
            if ($this->sales->bids->where('user_id', Auth::user()->id)->where('sales_character_id', '!=', $this->id)->count()) {
                if ($msg) {
                    return 'You have already made an offer for a character in this sale.';
                }

                return false;
            }
        }

        switch ($this->type) {
            case 'flatsale':case 'pwyw':
                //if a claim is pending
                if ($this->bids->count()) {
                    if ($msg) {
                        return 'A claim for this character is already pending.';
                    }

                    return false;
                }

                break;
            case 'raffle':case 'flaffle':
                //can only enter raffle once
                if ($this->bids->where('user_id', Auth::user()->id)->count()) {
                    if ($msg) {
                        return 'You have already entered the raffle.';
                    }

                    return false;
                }

                break;
            case 'auction':
                //pending AB
                if ($this->bids->where('data.autobuy', true)->count()) {
                    if ($msg) {
                        return 'An autobuy is currently pending.';
                    }

                    return false;
                }

                break;
        }
        return true;

    }

    /**
     * Check if the user can manage a bid
     *
     * @return App\Models\Character\CharacterImage
     */
    public function getCanManageBidAttribute()
    {
        if (! Auth::check()) {
            return false;
        }
        //only staff or ga
        if ($this->isStaffOrGuest) {
            return true;
        }

        //or the user who owns the sale
        if (! $this->sales->id_admin && $this->sales->user_id == Auth::user()->id) {
            return true;
        }

        //can manage a bid.
        return false;

    }

    /**
     * Get if users can view a sales' bids
     *
     * @return App\Models\Character\CharacterImage
     */
    public function getCanViewBidAttribute()
    {
        //For some sales, the bids may or not be public
        //for example, there's no reason for users to see a pending flatsale
        //but auctions and XTA are a bit more reliant on that

        if (! Auth::check()) {
            return false;
        }

        //let's let staff or GA always view an archive for record keeping
        if ($this->isStaffOrGuest) {
            return true;
        }
        //cannot place offers on a closed chara or sale
        if (! $this->isActive) {
            return false;
        }

        //user who made the bid
        if ($this->sales->user_id == Auth::user()->id) {
            return true;
        }

        //finally check individual type
        switch ($this->type) {
            case 'flatsale':case 'raffle':case 'flaffle':case 'pwyw':
                return false;
                break;
            case 'auction':case 'ota':case 'xta':case 'raffle':
                return true;
                break;
        }
        return true;
    }

    /**
     * Gets the Sales post URL.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->sales->is_admin) {
            return url('sales/character/bids/' . $this->id);
        }

        return url('user-sales/character/bids/' . $this->id);
    }

    /**
     * Get the display name for the bid
     *
     * @return string
     */
    public function bidType($forceSing = false)
    {
        switch ($this->attributes['type']) {
            case 'flatsale':case 'pwyw':
                $name = 'claim';
                break;
            case 'auction':
                $name = 'bid';
                break;
            case 'ota':
                $name = 'offer';
                break;
            case 'xta':case 'flaffle':case 'raffle':
                $name = 'entry';
                break;
        }
        if ($forceSing) {
            return $name;
        }

        if (! count($this->bids) || count($this->bids) > 1) {
            if ($name == 'entry') {
                return 'entries';
            } else {
                return $name . 's';
            }

        }
        return $name;
    }

    /**
     * Get if the sale is active anymore
     *
     * @return string
     */
    public function getIsActiveAttribute()
    {
        //closed chara or sale
        if (! $this->is_open || ! $this->sales->is_open) {
            return false;
        }
        if ($this->type == 'auction') {
            if ($this->auctionEnd->isPast()) {
                return false;
            }

        }
        return true;
    }

    /**
     * Get the last bid/offer for the character.
     *
     * @return string
     */
    public function getLastBidAttribute()
    {
        if (! count($this->bids)) {
            return null;
        }

        return $this->bids->last();
    }

        /**
     * Get the last bid/offer for the character's previous bid before that (for auctions).
     *
     * @return string
     */
    public function getPreviousBidAttribute()
    {
        if (! count($this->bids)) {
            return null;
        }

        if(!$this->bids()->where('id', '<', $this->lastBid->id)->orderBy('id', 'DESC')->first()){
return null;
        }

        return $this->bids()->where('id', '<', $this->lastBid->id)->orderBy('id', 'DESC')->first();
    }

    /**
     * Get the minimum bid that can be made for a character.
     *
     * @return string
     */
    public function getMinBidAttribute()
    {
        switch ($this->attributes['type']) {
            case 'flatsale':case 'pwyw':
                return 'claim';
                break;
            case 'auction':
                if (! $this->lastBid) {
                    return $this->data['starting_bid'];
                }

                return $this->lastBid->data['bid'] + $this->data['min_increment'];
                break;
            case 'ota':
                return 'offer';
                break;
            case 'xta':case 'flaffle':case 'raffle':
                return 'entry';
                break;
        }
    }

    /**
     * Determine the time at which an auction will end.
     *
     * @return string
     */
    public function getAuctionEndAttribute()
    {
        if (isset($this->data['snipe_period']) && $this->data['snipe_guard'] && $this->lastBid) {
            if (isset($this->lastBid->data['snipe'])) {
                //if it's a continuing snipe chain
                if ($this->previousBid) {
                    return Carbon::parse($this->previousBid->created_at)->max($this->previousBid->created_at)->addMinutes($this->data['snipe_guard']);

                } else {
                    //if this is the first snipe bid
                    return Carbon::parse($this->data['end_point'])->max($this->lastBid->created_at)->addMinutes($this->data['snipe_guard']);
                }

            }
        }
        return Carbon::parse($this->data['end_point']);
    }

    /**
     * Determine the recipient for the sale's funds
     *
     * @return string
     */
    public function getSalesRecipientAttribute()
    {
        if ($this->recipient_id) {
            return $this->recipient;
        }

        return $this->sales->user;

    }

    /**
     * Get if auth::user is staff or GA
     *
     * @return App\Models\Character\CharacterImage
     */
    public function getIsStaffOrGuestAttribute()
    {
        //some of these perms won't involve the bid user so we need a new check lol
        if (! Auth::check()) {
            return false;
        }

        //staff
        if (Auth::user()->hasPower('manage_sales')) {
            return true;
        }

        //the GA/recipient
        if (Settings::get('ga_can_manage_bids') && $this->recipient_id == Auth::user()->id) {
            return true;
        }

        return false;
    }

}
