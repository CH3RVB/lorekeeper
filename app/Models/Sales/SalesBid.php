<?php
namespace App\Models\Sales;

use App\Models\Character\Character;
use App\Models\Character\CharacterTransfer;
use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\User\User;
use App\Traits\Commentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SalesBid extends Model
{
    use Commentable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'sales_id', 'sales_character_id', 'paypal_email', 'is_featured', 'status', 'staff_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_bids';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

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
        'sales_id' => 'required',
        'user_id'  => 'required',
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
     * Get the character this is attached to.
     */
    public function salesCharacter()
    {
        return $this->belongsTo(SalesCharacter::class, 'sales_character_id');
    }

    /**
     * Get the user who made the bid.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the bid's pending transfer.
     */
    public function transfer()
    {
        return $this->belongsTo(CharacterTransfer::class, 'sales_bid_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the Sales post URL.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->sales->is_admin) {
            return url('sales/bids/' . $this->id);
        }

        return url('user-sales/bids/' . $this->id);
    }

    /**
     * Get the display name for the bid
     *
     * @return string
     */
    public function type($forceSing = false)
    {
        return $this->salesCharacter->bidType($forceSing);
    }

    /**
     * Get formatted pricing information.
     *
     * @return string
     */
    public function getPriceAttribute()
    {
        if ($this->data['currency'] == 'paypal') {
            return $this->salesCharacter->data['irl_currency'] .' '. $this->data['bid'];
        }

        $currency = Currency::find(isset($this->data['currency']));
        if (! $currency) {
            return 'Deleted Currency';
        }
        return $currency->display($this->data['bid']);

    }

    /**
     * Check if the user can manage a bid
     *
     * @return string
     */
    public function getCanManageBidAttribute()
    {
        return $this->salesCharacter->canManageBid;

    }

    /**
     * Get if auth::user is staff or GA
     *
     * @return string
     */
    public function getIsStaffOrGuestAttribute()
    {
        return $this->salesCharacter->isStaffOrGuest;

    }

    /**
     * Determine if a user can view a bid
     *
     * @return string
     */
    public function getCanViewBidAttribute()
    {
        //user who made the bid
        if ($this->user_id == Auth::user()->id) {
            return true;
        }
        return $this->salesCharacter->canViewBid;

    }

    /**
     * return if the bid is being made with paypal or site currency
     *
     * @return string
     */
    public function getPaymentTypeAttribute()
    {
        if ($this->data['currency'] == 'paypal') {
            return 'irl';
        }
        return 'onsite';
    }

    /**
     * Get if the auth::user made/owns the bid
     *
     * @return string
     */
    public function getIsBidOwnerAttribute()
    {
        //not checking for admin here cus we already did that
        if ($this->user_id == Auth::user()->id) {
            return true;
        }

        return false;

    }

    /**
     * Determine the recipient for the sale's funds
     *
     * @return string
     */
    public function getRecipientAttribute()
    {
        return $this->salesCharacter->salesRecipient;

    }

}
