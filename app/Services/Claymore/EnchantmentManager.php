<?php namespace App\Services\Claymore;

use Carbon\Carbon;
use App\Services\Service;

use Auth;
use DB;
use Config;
use Notifications;

use Illuminate\Support\Arr;

use App\Models\User\User;
use App\Models\Claymore\Enchantment;
use App\Models\User\UserEnchantment;
use App\Models\Claymore\Gear;
use App\Models\User\UserGear;
use App\Models\Claymore\Weapon;
use App\Models\User\UserWeapon;
use App\Services\Stat\StatManager;
use App\Services\CurrencyManager;
use App\Models\User\UserCurrency;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;

class EnchantmentManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned enchantments.
    |
    */

    /**
     * Grants an enchantment to multiple users.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $staff
     * @return bool
     */
    public function grantEnchantments($data, $staff)
    {
        DB::beginTransaction();

        try {
            foreach($data['quantities'] as $q) {
                if($q <= 0) throw new \Exception("All quantities must be at least 1.");
            }

            // Process names
            $users = User::find($data['names']);
            if(count($users) != count($data['names'])) throw new \Exception("An invalid user was selected.");

            $keyed_quantities = [];
            array_walk($data['enchantment_ids'], function($id, $key) use(&$keyed_quantities, $data) {
                if($id != null && !in_array($id, array_keys($keyed_quantities), TRUE)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process enchantment
            $enchantments = Enchantment::find($data['enchantment_ids']);
            if(!count($enchantments)) throw new \Exception("No valid enchantments found.");

            foreach($users as $user) {
                foreach($enchantments as $enchantment) {
                    if($this->creditEnchantment($staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $enchantment, $keyed_quantities[$enchantment->id]))
                    {
                        Notifications::create('ENCHANTMENT_GRANT', $user, [
                            'enchantment_name' => $enchantment->name,
                            'enchantment_quantity' => $keyed_quantities[$enchantment->id],
                            'sender_url' => $staff->url,
                            'sender_name' => $staff->name
                        ]);
                    }
                    else
                    {
                        throw new \Exception("Failed to credit enchantments to ".$user->name.".");
                    }
                }
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Transfers an enchantment stack between users.
     *
     * @param  \App\Models\User\User      $sender
     * @param  \App\Models\User\User      $recipient
     * @param  \App\Models\User\UserEnchantment  $stack
     * @return bool
     */
    public function transferStack($sender, $recipient, $stack)
    {
        DB::beginTransaction();

        try {
            if(!$sender->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
            if(!$stack) throw new \Exception("Invalid enchantment selected.");
            if($stack->user_id != $sender->id && !$sender->hasPower('edit_inventories')) throw new \Exception("You do not own this enchantment.");
            if($stack->user_id == $recipient->id) throw new \Exception("Cannot send an enchantment to the enchantment's owner.");
            if(!$recipient) throw new \Exception("Invalid recipient selected.");
            if(!$recipient->hasAlias) throw new \Exception("Cannot transfer enchantments to a non-verified member.");
            if($recipient->is_banned) throw new \Exception("Cannot transfer enchantments to a banned member.");
            if((!$stack->enchantment->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_inventories')) throw new \Exception("This enchantment cannot be transferred.");

            $oldUser = $stack->user;
            if($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack)) 
            {
                Notifications::create('ENCHANTMENT_TRANSFER', $recipient, [
                    'enchantment_name' => $stack->enchantment->name,
                    'enchantment_quantity' => 1,
                    'sender_url' => $sender->url,
                    'sender_name' => $sender->name
                ]);
                if($stack->user_id != $sender->id) 
                    Notifications::create('FORCED_ENCHANTMENT_TRANSFER', $oldUser, [
                        'enchantment_name' => $stack->enchantment->name,
                        'enchantment_quantity' => 1,
                        'sender_url' => $sender->url,
                        'sender_name' => $sender->name
                    ]);
                return $this->commitReturn(true);
            }
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an enchantment stack.
     *
     * @param  \App\Models\User\User      $user
     * @param  \App\Models\User\UserEnchantment  $stack
     * @return bool
     */
    public function deleteStack($user, $stack)
    {
        DB::beginTransaction();

        try {
            if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
            if(!$stack) throw new \Exception("Invalid enchantment selected.");
            if($stack->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this enchantment.");

            $oldUser = $stack->user;

            if($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack)) 
            {
                if($stack->user_id != $user->id) 
                    Notifications::create('ENCHANTMENT_REMOVAL', $oldUser, [
                        'enchantment_name' => $stack->enchantment->name,
                        'enchantment_quantity' => 1,
                        'sender_url' => $user->url,
                        'sender_name' => $user->name
                    ]);
                return $this->commitReturn(true);
            }
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Names a enchantment stack.
     *
     * @param  \App\Models\User\User        $owner
     * @param  \App\Models\User\UserEnchantment
     * @param  int                                                            $quantities
     * @return bool
     */
    public function nameStack($enchantment, $name)
    {
        DB::beginTransaction();

        try {
                $user = Auth::user();
                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$enchantment) throw new \Exception("An invalid enchantment was selected.");
                if($enchantment->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this enchantment.");

                $enchantment['enchantment_name'] = $name;
                $enchantment->save();
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

  /**
     * attaches a enchantment stack.
     *
     * @param  \App\Models\User\User $owner
     * @param  \App\Models\User\UserEnchantment $stacks
     * @param  int       $quantities
     * @return bool
     */
    public function attachEnchantStack($enchantment, $id, $type)
    {
        DB::beginTransaction();

        try {
                $user = Auth::user();
                if($id == NULL) throw new \Exception("No ".$type." selected.");
                if($type == 'gear'){
                    $claymore = UserGear::find($id);
                    $claymorestack = UserGear::withTrashed()->where('id', $id)->with('gear')->first();
                }
                else{
                    $claymore = UserWeapon::find($id);
                    $claymorestack = UserWeapon::withTrashed()->where('id', $id)->with('weapon')->first();
                }
                
                //check user hasn't exceeded slot limit
                $enchantments = UserEnchantment::where('id',$claymore)->first();
                $currentench = $claymore->enchantments->count();
                $check = $currentench >= $claymore->slots;
                if($check) throw new \Exception('You have exceeded the maximum slot count. Add slots to add more enchantments!');

                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$enchantment) throw new \Exception("An invalid enchantment was selected.");
                if($enchantment->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this enchantment.");
                if(!$claymore) throw new \Exception("An invalid ".$type." was selected.");
                if($claymore->user_id !== $user->id && !$user->hasPower('edit_inventories'))throw new \Exception("You do not own this ".$type.".");
                // imposes a limit of duplicate enchantments if uncommented, if uncommented, the same enchantment will not be able to be attached to a claymore twice
                // if($claymore->enchantments()->where('enchantment_id', $enchantment->enchantment->id)->exists()) throw new \Exception("This type of enchantment is already attached to the ".$type." selected.");

                if($type == 'gear'){
                    $enchantment['gear_stack_id'] = $claymorestack->id;
                }
                else{
                    $enchantment['weapon_stack_id'] = $claymorestack->id;
                }
                $enchantment['attached_at'] = Carbon::now();
                $enchantment->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * detaches a enchantment stack.
     *
     */
    public function detachEnchantStack($enchantment)
    {
        DB::beginTransaction();

        try {
                $user = Auth::user();
                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$enchantment) throw new \Exception("An invalid enchantment was selected.");
                if($enchantment->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this enchantment.");

                $enchantment['gear_stack_id'] = null;
                $enchantment['weapon_stack_id'] = null;
                $enchantment['attached_at'] = null;
                $enchantment->save();
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * attaches a enchantment stack.
     *
     * @param  \App\Models\User\User $owner
     * @param  \App\Models\User\UserEnchantment $stacks
     * @param  int       $quantities
     * @return bool
     */
    public function attachEnchantWeaponStack($enchantment, $id)
    {
        DB::beginTransaction();

        try {

            

                $user = Auth::user();
                if($id == NULL) throw new \Exception("No weapon selected.");
                $weapon = UserWeapon::find($id);
                $weaponstack = UserWeapon::withTrashed()->where('id', $id)->with('weapon')->first();

                //check weapon hasn't exceeded slot limit
                $enchantments = UserEnchantment::where('id',$weapon)->first();
                $currentench = $weapon->enchantments->count();
                $check = $currentench >= $weapon->slots;
                if($check) throw new \Exception('You have exceeded the maximum slot count. Add slots to add more enchantments!');

                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$enchantment) throw new \Exception("An invalid enchantment was selected.");
                if($enchantment->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this enchantment.");
                if(!$weapon) throw new \Exception("An invalid weapon was selected.");
                if($weapon->user_id !== $user->id && !$user->hasPower('edit_inventories'))throw new \Exception("You do not own this weapon.");
                // imposes a limit of duplicate enchantments if uncommented, if uncommented, the same enchantment will not be able to be attached to a weapon twice
                // if($weapon->enchantments()->where('enchantment_id', $enchantment->enchantment->id)->exists()) throw new \Exception("This type of enchantment is already attached to the weapon selected.");
                

                $enchantment['weapon_stack_id'] = $weaponstack->id;
                $enchantment['attached_at'] = Carbon::now();
                $enchantment->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * edits enchantments
     */
    public function editEnchantment($id, $gear)
    {
        DB::beginTransaction();

        try {

                $gear['enchantment_id'] = $id;
                $gear->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * @param \App\Models\User\UserEnchantment
     * @return \App\Models\User\UserEnchantment
     */
    public function upgrade($enchantment, $isStaff = false)
    {        
        DB::beginTransaction();

        try {   
            
            if(!$isStaff) {
                if($enchantment->enchantment->currency_id != 0) {
                    $service = new CurrencyManager;

                    $currency = Currency::find($enchantment->enchantment->currency_id);
                    if(!$currency) throw new \Exception('Invalid currency set by admin.');

                    $user = User::find($enchantment->user_id);
                    if(!$service->debitCurrency($user, null, 'Enchantment Upgrade', 'Upgraded '.$enchantment->enchantment->displayName.'', $currency, $enchantment->enchantment->cost)) throw new \Exception('Could not debit currency.');                
                }
                elseif($enchantment->enchantment->currency_id == 0)
                {
                    $service = new StatManager;
                    
                    $user = User::find($enchantment->user_id);
                    if(!$service->debitStat($user, null, 'Enchantment Upgrade', 'Upgraded '.$enchantment->enchantment->displayName.'', $enchantment->enchantment->cost)) throw new \Exception('Could not debit points.');                
                }
            }

            $enchantment->enchantment_id = $enchantment->enchantment->parent_id;
            $enchantment->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Credits an enchantment to a user.
     *
     * @param  \App\Models\User\User  $sender
     * @param  \App\Models\User\User  $recipient
     * @param  string                 $type 
     * @param  array                  $data
     * @param  \App\Models\Enchantment\Enchantment  $enchantment
     * @param  int                    $quantity
     * @return bool
     */
    public function creditEnchantment($sender, $recipient, $type, $data, $enchantment, $quantity)
    {
        DB::beginTransaction();

        try {
            for($i = 0; $i < $quantity; $i++) UserEnchantment::create(['user_id' => $recipient->id, 'enchantment_id' => $enchantment->id, 'data' => json_encode($data)]);
            if($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, null, $type, $data['data'], $enchantment->id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Moves an enchantment stack from one user to another.
     *
     * @param  \App\Models\User\User      $sender
     * @param  \App\Models\User\User      $recipient
     * @param  string                     $type 
     * @param  array                      $data
     * @param  \App\Models\User\UserEnchantment  $enchantment
     * @return bool
     */
    public function moveStack($sender, $recipient, $type, $data, $stack)
    {
        DB::beginTransaction();

        try {
            $stack->user_id = $recipient->id;
            $stack->save();

            if($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, $stack->id, $type, $data['data'], $stack->enchantment_id, 1)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Debits an enchantment from a user.
     *
     * @param  \App\Models\User\User      $user
     * @param  string                     $type 
     * @param  array                      $data
     * @param  \App\Models\Enchantment\UserEnchantment  $stack
     * @return bool
     */
    public function debitStack($user, $type, $data, $stack)
    {
        DB::beginTransaction();

        try {
            $stack->delete();

            if($type && !$this->createLog($user ? $user->id : null, null, $stack->id, $type, $data['data'], $stack->enchantment_id, 1)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Creates an inventory log.
     *
     * @param  int     $senderId
     * @param  int     $recipientId
     * @param  int     $stackId
     * @param  string  $type 
     * @param  string  $data
     * @param  int     $quantity
     * @return  int
     */
    public function createLog($senderId, $recipientId, $stackId, $type, $data, $enchantmentId, $quantity)
    {
        return DB::table('user_enchantments_log')->insert(
            [       
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'stack_id' => $stackId,
                'log' => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type' => $type,
                'data' => $data, // this should be just a string
                'enchantment_id' => $enchantmentId,
                'quantity' => $quantity,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
    }
}