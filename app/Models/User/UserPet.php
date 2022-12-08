<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pet\PetDrop;

class UserPet extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'pet_id', 'user_id', 'attached_at'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_pets';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the pet associated with this pet stack.
     */
    public function pet()
    {
        return $this->belongsTo('App\Models\Pet\Pet');
    }

    /**
     * Get the variant associated with this pet stack.
     */
    public function variant()
    {
        return $this->belongsTo('App\Models\Pet\PetVariant','variant_id');
    }


    public function character()
    {
        return $this->belongsTo('App\Models\Character\Character', 'chara_id');
    }


    /**
     * Get the pet's pet drop data.
     */
    public function drops()
    {
        if(!PetDrop::where('user_pet_id', $this->id)->first()) {
            $drop = new PetDrop;
            $drop->createDrop($this->id);
        }
        elseif(!PetDrop::where('user_pet_id', $this->id)->where('drop_id', $this->pet->dropData->id)->first()) {
            PetDrop::where('user_pet_id', $this->id)->delete;
            $drop = new PetDrop;
            $drop->createDrop($this->id);
        }
        return $this->hasOne('App\Models\Pet\PetDrop', 'user_pet_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute()
    {
        if(!isset($this->data['disallow_transfer']) && $this->pet->allow_transfer) return true;
        return false;
    }

    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'user_pets';
    }
}
