<?php

namespace App\Models\Pet;

use Config;
use DB;
use Carbon\Carbon;
use Notifications;
use App\Models\Model;

use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Character\Character;

use App\Models\User\UserPet;
use App\Models\Pet\Pet;
use App\Models\Pet\PetVariant;
use App\Models\Pet\PetDrop;

use App\Models\Currency\Currency;
use App\Models\Item\Item;

class PetDropData extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_id', 'variant_id', 'parameters', 'data', 'is_active'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_drop_data';

    /**
     * Validation rules for pet creation.
     *
     * @var array
     */
    public static $createRules = [
        'pet_id' => 'required|unique:pet_drop_data',
        'drop_frequency' => 'required',
        'drop_interval' => 'required'
    ];

    /**
     * Validation rules for pet updating.
     *
     * @var array
     */
    public static $updateRules = [
        'pet_id' => 'required',
        'drop_frequency' => 'required',
        'drop_interval' => 'required'
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the pet to which the data pertains.
     */
    public function pet()
    {
        return $this->belongsTo('App\Models\Pet\Pet', 'pet_id');
    }

    /**
     * Get the pet to which the data pertains.
     */
    public function user_pet()
    {
        return $this->belongsTo('App\Models\Pet\Pet', 'pet_id');
    }

    /**
     * Get any pet drops using this data.
     */
    public function petDrops()
    {
        return $this->hasMany('App\Models\Pet\PetDrop', 'drop_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the parameter attribute as an associative array.
     *
     * @return array
     */
    public function getUrlAttribute()
    {
        return url('admin/data/pet-drops/edit/'.$this->id);
    }

    /**
     * Get the parameter attribute as an associative array.
     *
     * @return array
     */
    public function getParametersAttribute()
    {
        if(isset($this->attributes['parameters'])) return json_decode($this->attributes['parameters'], true);
        else return null;
    }

    /**
     * Get the parameter attribute as an array with the keys and values the same.
     *
     * @return array
     */
    public function getParameterArrayAttribute()
    {
        foreach($this->parameters as $parameter=>$weight) $paramArray[$parameter] = $parameter;
        return $paramArray;
    }

    /**
     * Get the parameter attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        if(isset($this->attributes['data'])) return json_decode($this->attributes['data'], true);
        else return null;
    }

    /**
     * Check if the drop data is active or not.
     *
     * @return array
     */
    public function getIsActiveAttribute()
    {
        return $this->attributes['is_active'];
    }

    /**
     * Retrieve the drop data's cap.
     *
     * @return array
     */
    public function getCapAttribute()
    {
        return isset($this->data['cap']) ? $this->data['cap'] : null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Rolls a group for a pet.
     *
     * @return string
     */
    public function rollParameters()
    {
        $parameters = $this->parameters;
        $totalWeight = 0;
        foreach($parameters as $parameter=>$weight) $totalWeight += $weight;

        for($i = 0; $i < 1; $i++)
        {
            $roll = mt_rand(0, $totalWeight - 1);
            $result = null;
            $prev = null;
            $count = 0;
            foreach($parameters as $parameter=>$weight)
            {
                $count += $weight;

                if($roll < $count)
                {
                    $result = $parameter;
                    break;
                }
                $prev = $parameter;
            }
            if(!$result) $result = $prev;
        }
        return $result;
    }

}
