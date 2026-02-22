<?php

namespace App\Models\Sales;

use App\Models\Model;
use App\Models\User\User;
use App\Traits\Commentable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\Feed\FeedItem;

class Sales extends Model {
    use Commentable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'text', 'parsed_text', 'title', 'is_visible', 'post_at',
        'is_open', 'comments_open_at','is_admin','limit_setting','is_rolled'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'post_at'          => 'datetime',
        'comments_open_at' => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'title' => 'required|between:3,100',
        'text'  => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'title' => 'required|between:3,100',
        'text'  => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who created the Sales post.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the characters associated with the sales post.
     */
    public function characters() {
        return $this->hasMany(SalesCharacter::class, 'sales_id');
    }

    /**
     * Get all valid sales bids regardless of character
     */
    public function bids() {
        return $this->hasMany(SalesBid::class, 'sales_id')->whereNull('deleted_at')->where('status', '!=', 'Rejected');
    }

    /**
     * Get ALL sales bids regardless of character and validity
     */
    public function allBids() {
        return $this->hasMany(SalesBid::class, 'sales_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('manage_sales')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to only include posts that are scheduled to be posted and are ready to post.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldBeVisible($query) {
        return $query->whereNotNull('post_at')->where('post_at', '<', Carbon::now())->where('is_visible', 0);
    }

    /**
     * Scope a query to sort sales in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('title', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort sales by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query, $reverse = false) {
        return $query->orderBy('id', $reverse ? 'ASC' : 'DESC');
    }

    /**
     * Scope a query to sort sales by bump date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBump($query, $reverse = false) {
        return $query->orderBy('updated_at', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to only include staff sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query) {

        return $query->where('is_admin', 1);
    }

    /**
     * Scope a query to only include user sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsers($query) {

        return $query->where('is_admin', 0);
    }

    /**
     * Scope a query to only include user sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query) {

        return $query->where('is_open', 1);
    }

    /**
     * Scope a query to only include user sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query) {

        return $query->where('is_open', 0);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the Sales slug.
     *
     * @return bool
     */
    public function getSlugAttribute() {
        return $this->id.'.'.Str::slug($this->title);
    }

    /**
     * Displays the Sales post title, linked to the Sales post itself.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'"> ['.($this->is_open ? (isset($this->comments_open_at) && $this->comments_open_at > Carbon::now() ? 'Preview' : 'Open') : 'Closed').'] '.$this->title.'</a>';
    }

    /**
     * Gets the Sales post URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        if($this->is_admin) return url('sales/'.$this->slug);
        return url('user-sales/'.$this->slug);
    }
}
