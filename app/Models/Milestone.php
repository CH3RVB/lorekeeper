<?php

namespace App\Models;

use App\Models\Item\ItemCategory;
use App\Services\MilestoneService;

class Milestone extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'milestone', 'has_image', 'description', 'parsed_description', 'category_id', 'subcategory_id', 'is_active', 'is_visible', 'summary', 'milestone_type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'milestones';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************
        RELATIONS
    **********************************************************************************************/

    /**
     * Get the users who have this milestone.
     */
    public function users() {
        return $this->belongsToMany('App\Models\User\User', 'user_milestones')->withPivot('id');
    }

    /**
     * Get the category the milestone belongs to.
     */
    public function category() {
        switch ($this->milestone_type) {
            case 'Item':
                return $this->belongsTo(ItemCategory::class, 'category_id');
                break;
        }

        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    /**
     * Get the subcategory the milestone belongs to.
     *
     * @param mixed $query
     * @param mixed $reverse
     */
    /*
    public function subcategory()
    {
               switch ($this->milestone_type) {
            case 'Item':
                return $this->belongsTo(ItemCategory::class, 'subcategory_id');
                break;
        }
        return $this->belongsTo(ItemCategory::class, 'subcategory_id');
    }
    */

    /**********************************************************************************************
        SCOPES
    *****************************************************************************************

    /**
     * Scope a query to sort milestones in milestone order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('milestone', $reverse ? 'ASC' : 'DESC');
    }

    /**
     * Scope a query to sort milestones by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only visible milestones.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to show only visible milestones.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to sort items in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $t
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query, $t = null) {
        if (isset($t)) {
            $path = (new MilestoneService)->milestonePath($this->milestone_type);
            if (class_exists($path)) {
                if ($path::all()->count()) {
                    return $query->where('milestone_type', $t)->orderBy($path::select('sort')->whereColumn('milestones.category_id', strtolower($t).'_categories.id'), 'DESC');
                }
            }
        }

        return $query;
    }

    /**********************************************************************************************
        ACCESSORS
    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        // holy concatenation batman
        return '<a href="'.$this->idUrl.'" class="display-item">'.$this->milestone.' '.
        ($this->category ? $this->category->name.' ' : '').$this->milestone_type.'s</a>';
    }

    /**
     * Gets the URL of the individual milestone's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/milestones/'.$this->id);
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/milestones';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'milestones';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/milestones/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**
     * Get if the milestone can be completed.
     *
     * @param mixed $user
     */
    public function canClaim($user) {
        // cannot redeem if inactive
        if (!$this->is_active) {
            return false;
        }

        // not visible
        if (!$this->is_visible && !$user->hasPower('edit_data')) {
            return false;
        }

        // already claimed rewards/completed
        if ($user->milestones->contains($this)) {
            return false;
        }

        // now we can actually check the counts...

        // get all user->owned
        $owned = (new MilestoneService)->getOwned($this->milestone_type, $user);

        // if category set, filter by category
        if (isset($this->category_id) && $this->category) {
            switch ($this->milestone_type) {
                case 'Item':
                    $owned = $owned->whereRelation('item', 'item_category_id', $this->category_id);
                    break;
                case 'Prompt':
                    $owned = $owned->whereRelation('prompt', 'prompt_category_id', $this->category_id);
                    break;
            }
        }

        /*
            //if subcategory set, filter by subcategory
            if (isset($this->subcategory_id) && $this->subcategory) {
                switch ($this->milestone_type) {
                    case 'Item':
                        $owned = $owned->whereRelation('item','item_subcategory_id', $this->subcategory_id);
                        break;
                }
            }
        */

        if (!isset(config('lorekeeper.milestones.'.$this->milestone_type)['override_key'])) {
            $owned = count($owned->get()->unique(strtolower($this->milestone_type.'_id')));
        } else {
            // abort getting unique keys
            switch ($this->milestone_type) {
                case 'Currency':
                    // assuming this is what most sites will want to do, since different currencies themselves aren't usually the goal. it's about the quantity of individual currencies.
                    $owned = $owned->get()->pluck('quantity')->sum();
                    break;
                default:
                    $owned = count($owned->get());
                    break;
            }
        }

        // get final count and compare to req
        if ($owned >= $this->milestone) {
            return true;
        }

        return false;
    }
}
