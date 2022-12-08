<?php

namespace App\Models\Pet;

use Config;
use DB;
use Carbon\Carbon;
use Notifications;
use App\Models\Model;

use App\Models\User\User;
use App\Models\User\UserPet;
use App\Models\Pet\Pet;
use App\Models\Pet\PetDropData;
use App\Models\Item\Item;
use App\Models\Item\ItemLog;

class PetDrop extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'drop_id', 'user_pet_id', 'parameters', 'drops_available', 'next_day'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_drops';

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['next_day'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the associated user pet.
     */
    public function user_pet()
    {
        return $this->belongsTo('App\Models\User\UserPet', 'user_pet_id');
    }

    /**
     * Get the category the user pet belongs to.
     */
    public function dropData()
    {
        return $this->belongsTo('App\Models\Pet\PetDropData', 'drop_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include drops that require updating.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresUpdate($query)
    {
        return $query->whereIn('drop_id', PetDropData::where('is_active', 1)->pluck('id')->toArray())->where('next_day', '<', Carbon::now());
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the item(s) a given user pet should be dropping.
     *
     */
    public function getPetItemAttribute()
    {
        // Collect data from the drop data about what item this species drops.
        $itemsData = $this->dropData->data['items'];
        $petItem = isset($itemsData['pet']) && isset($itemsData['pet'][$this->parameters]) ? Item::find($itemsData['pet'][$this->parameters]['item_id']) : null;
        if($petItem) return $petItem;
        else return null;
    }

    /**
     * Get quantity or quantity range for pet drop.
     *
     */
    public function getPetQuantityAttribute()
    {
        if($this->petItem) {
            $itemsData = $this->dropData->data['items'];
            $min = $itemsData['pet'][$this->parameters]['min'];
            $max = $itemsData['pet'][$this->parameters]['max'];
            if($min == $max) return $min;
            else return $min.'-'.$max;
        }
    }

    /**
     * Get the item(s) a given user pet should be dropping.
     *
     */
    public function getVariantItemAttribute()
    {
        // Collect data from the drop data about what item this variant drops.
        $itemsData = $this->dropData->data['items'];
        $variantItem = isset($this->user_pet->variant_id) && isset($itemsData[$this->user_pet->variant_id][$this->parameters]) ? Item::find($itemsData[$this->user_pet->variant_id][$this->parameters]['item_id']) : null;
        if($variantItem) return $variantItem;
        else return null;
    }

    /**
     * Get quantity or quantity range for species drop.
     *
     */
    public function getVariantQuantityAttribute()
    {
        if($this->variantItem) {
            $itemsData = $this->dropData->data['items'];
            $min = $itemsData[$this->user_pet->variant_id][$this->parameters]['min'];
            $max = $itemsData[$this->user_pet->variant_id][$this->parameters]['max'];
            if($min == $max) return $min;
            else return $min.'-'.$max;
        }
    }

    /**
     * Get the item(s) a given user pet should be dropping.
     *
     */
    public function getItemsAttribute()
    {
        // Collect resulting items
        $items = collect([]);
        if($this->petItem) $items = $items->concat([$this->petItem]);
        if($this->variantItem) $items = $items->concat([$this->variantItem]);

        return $items;
    }

    /**
     * Get the display of the group a user pet belongs to, so long as the species has more than one.
     *
     */
    public function getGroupAttribute()
    {
        if(count($this->dropData->parameters) > 1) return ' ('.$this->parameters.')';
        else return null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Create drop info for a user pet.
     *
     * @param int              $id
     */
    public function createDrop($id, $parameters = null)
    {
        $user_pet = UserPet::find($id);
        $dropData = $user_pet->pet->dropData;
        $drop = $this->create([
            'drop_id' => $dropData->id,
            'user_pet_id' => $id,
            'parameters' => $parameters ? $parameters : $dropData->rollParameters(),
            'drops_available' => 0,
            'next_day' => Carbon::now()->add($dropData->data['frequency']['frequency'], $dropData->data['frequency']['interval'])->startOf($dropData->data['frequency']['interval']),
        ]);
    }
}
