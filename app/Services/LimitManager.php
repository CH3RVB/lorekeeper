<?php

namespace App\Services;

use App\Models\Character\CharacterItem;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\LimitSettings;
use App\Models\ObjectLimit;
use App\Models\Recipe\Recipe;
use App\Models\Submission\Submission;
use App\Models\User\User;
use App\Models\User\UserItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LimitManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Limit Maker Service
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of limits
    |
     */

    /**
     * Edit limit
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editLimits($object, $data)
    {

        DB::beginTransaction();
        try {

            // We're going to remove all limits and reattach them with the updated data

            $object->objectLimits()->delete();

            if (isset($data['limit_id'])) {
                foreach ($data['limit_id'] as $key => $id) {
                    ObjectLimit::create([
                        'object_id' => $object->id,
                        'object_type' => class_basename($object),
                        'limit_type' => $data['limit_type'][$key],
                        'limit_id' => $id,
                        'quantity' => $data['quantity'][$key],
                    ]);
                }
            }

            //handle the settings model
            if (!$object->limitSettings) {
                LimitSettings::create([
                    'object_id' => $object->id,
                    'object_type' => class_basename($object),
                    'debit_limits' => isset($data['debit_limits']) ? 1 : 0,
                    'use_characters' => isset($data['use_characters']) ? 1 : 0,
                ]);
            } else {
                $object->limitSettings->update([
                    'debit_limits' => isset($data['debit_limits']) ? 1 : 0,
                    'use_characters' => isset($data['use_characters']) ? 1 : 0,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Approves a submission.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function checkLimits($object, $user, $character = null)
    {
        DB::beginTransaction();

        try {
            if (!$object) {
                throw new \Exception("Invalid object.");
            }

            if (!$user) {
                throw new \Exception("Invalid user.");
            }

            if (!$object->limitSettings) {
                LimitSettings::create([
                    'object_id' => $object->id,
                    'object_type' => class_basename($object),
                    'debit_limits' => 0,
                    'use_characters' => 0,
                ]);
            }

            if ($object->limitSettings->use_characters) {
                $owner = $character;
            } else {
                $owner = $user;
            }

            // Check for sufficient currencies
            $owner_currencies = $owner->getCurrencies(true);
            $currency_limits = $object->objectLimits->where('limit_type', 'Currency');
            foreach ($currency_limits as $limit) {
                $currency = $owner_currencies->where('id', $limit->limit_id)->first();
                if ($currency->quantity < $limit->quantity) {
                    flash('You require ' . $limit->limit->name . ' x' . $limit->quantity . ' to complete this action.')->error();
                    return false;
                }
            }

            if (!$object->limitSettings->onlyCurrency) {
                // Check for sufficient limits
                $plucked = $this->pluckLimits($owner, $object);
                if (!$plucked) {
                    return false;
                }
            }

            if ($object->limitSettings->debit_limits) {

                if (isset($plucked)) {
                    // Debit the limits
                    $service = new InventoryManager();
                    foreach ($plucked as $id => $quantity) {
                        $owner->logType == 'Character' ? $stack = CharacterItem::find($id) : $stack = UserItem::find($id);
                        if (!$service->debitStack($owner, 'Limit Checking', ['data' => 'Used in ' . $object->name . '.'], $stack, $quantity)) {
                            throw new \Exception('Items could not be removed.');
                        }

                    }
                }

                // Debit the currency
                $service = new CurrencyManager();
                foreach ($currency_limits as $limit) {
                    if (!$service->debitCurrency($user, null, 'Limit Checking', 'Used in ' . $object->name . '.', Currency::find($limit->limit_id), $limit->quantity)) {
                        throw new \Exception('Currency could not be debited.');
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', dd($e->getMessage()));
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Plucks stacks from a given Collection of user items that meet the crafting requirements of a recipe
     * If there are insufficient limits, null is returned
     *
     * @param  \Illuminate\Database\Eloquent\Collection     $owner_items
     * @param  \App\Models\Recipe\Recipe                    $recipe
     * @return array|null
     */
    public function pluckLimits($owner, $object, $selectedStacks = null)
    {
        if ($object->limitSettings->use_characters) {
            $owner_items = CharacterItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('character_id', $owner->id)->get();
        } else {
            $owner_items = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', $owner->id)->get();
        }

        $plucked = [];
        // foreach limit, search for a qualifying item, and select items up to the quantity, if insufficient continue onto the next entry
        foreach ($object->objectLimits->sortBy('limit_type') as $limit) {

            switch ($limit->limit_type) {
                case 'Item':
                    $stacks = $owner_items->where('item.id', $limit->limit_id);
                    break;
                case 'Currency':
                    continue 2;
            }

            $quantity_left = $limit->quantity;
            while ($quantity_left > 0 && count($stacks) > 0) {
                $stack = $stacks->pop();
                $plucked[$stack->id] = $stack->count >= $quantity_left ? $quantity_left : $stack->count;
                // Update the larger collection
                $owner_items = $owner_items->map(function ($s) use ($stack, $plucked) {
                    if ($s->id == $stack->id) {
                        $s->count -= $plucked[$stack->id];
                    }

                    if ($s->count) {
                        return $s;
                    } else {
                        return null;
                    }

                })->filter();
                $quantity_left -= $plucked[$stack->id];
            }
            // If there are no more eligible limits but the requirement is not fulfilled, the pluck fails
            if ($quantity_left > 0) {
                flash('You require ' . $limit->limit->name . ' x' . $limit->quantity . ' to complete this action.')->error();
                return null;
            }

        }
        return $plucked;
    }

}
