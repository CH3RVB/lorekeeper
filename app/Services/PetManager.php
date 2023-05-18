<?php namespace App\Services;

use Carbon\Carbon;
use App\Services\Service;

use Auth;
use DB;
use Config;
use Notifications;

use Illuminate\Support\Arr;

use App\Models\User\User;
use App\Models\Pet\Pet;
use App\Models\User\UserPet;
use App\Models\Character\Character;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseStock;

class PetManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned pets.
    |
    */

    /**
     * Grants an pet to multiple users.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $staff
     * @return bool
     */
    public function grantPets($data, $staff)
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
            array_walk($data['pet_ids'], function($id, $key) use(&$keyed_quantities, $data) {
                if($id != null && !in_array($id, array_keys($keyed_quantities), TRUE)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process pet
            $pets = Pet::find($data['pet_ids']);
            if(!count($pets)) throw new \Exception("No valid pets found.");

            foreach($users as $user) {
                foreach($pets as $pet) {
                    if($this->creditPet($staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $pet, $keyed_quantities[$pet->id]))
                    {
                        Notifications::create('PET_GRANT', $user, [
                            'pet_name' => $pet->name,
                            'pet_quantity' => $keyed_quantities[$pet->id],
                            'sender_url' => $staff->url,
                            'sender_name' => $staff->name
                        ]);
                    }
                    else
                    {
                        throw new \Exception("Failed to credit pets to ".$user->name.".");
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
     * Transfers an pet stack between users.
     *
     * @param  \App\Models\User\User      $sender
     * @param  \App\Models\User\User      $recipient
     * @param  \App\Models\User\UserPet  $stack
     * @return bool
     */
    public function transferStack($sender, $recipient, $stack)
    {
        DB::beginTransaction();

        try {
            if(!$sender->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
            if(!$stack) throw new \Exception("Invalid pet selected.");
            if($stack->user_id != $sender->id && !$sender->hasPower('edit_inventories')) throw new \Exception("You do not own this pet.");
            if($stack->user_id == $recipient->id) throw new \Exception("Cannot send an pet to the pet's owner.");
            if(!$recipient) throw new \Exception("Invalid recipient selected.");
            if(!$recipient->hasAlias) throw new \Exception("Cannot transfer pets to a non-verified member.");
            if($recipient->is_banned) throw new \Exception("Cannot transfer pets to a banned member.");
            if((!$stack->pet->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_inventories')) throw new \Exception("This pet cannot be transferred.");

            $oldUser = $stack->user;
            if($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack)) 
            {
                Notifications::create('PET_TRANSFER', $recipient, [
                    'pet_name' => $stack->pet->name,
                    'pet_quantity' => 1,
                    'sender_url' => $sender->url,
                    'sender_name' => $sender->name
                ]);
                if($stack->user_id != $sender->id) 
                    Notifications::create('FORCED_PET_TRANSFER', $oldUser, [
                        'pet_name' => $stack->pet->name,
                        'pet_quantity' => 1,
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
     * Deletes an pet stack.
     *
     * @param  \App\Models\User\User      $user
     * @param  \App\Models\User\UserPet  $stack
     * @return bool
     */
    public function deleteStack($user, $stack)
    {
        DB::beginTransaction();

        try {
            if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
            if(!$stack) throw new \Exception("Invalid pet selected.");
            if($stack->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this pet.");

            $oldUser = $stack->user;

            if($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack)) 
            {
                if($stack->user_id != $user->id) 
                    Notifications::create('PET_REMOVAL', $oldUser, [
                        'pet_name' => $stack->pet->name,
                        'pet_quantity' => 1,
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
     * Names a pet stack.
     *
     * @param  \App\Models\User\User        $owner
     * @param  \App\Models\User\UserPet
     * @param  int                                                            $quantities
     * @return bool
     */
    public function nameStack($pet, $name)
    {
        DB::beginTransaction();

        try {
                $user = Auth::user();
                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$pet) throw new \Exception("An invalid pet was selected.");
                if($pet->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this pet.");

                $pet['pet_name'] = $name;
                $pet->save();
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * attaches a pet stack.
     *
     * @param  \App\Models\User\User $owner
     * @param  \App\Models\User\UserPet $stacks
     * @param  int       $quantities
     * @return bool
     */
    public function attachStack($pet, $id)
    {
        DB::beginTransaction();

        try {
                $user = Auth::user();
                if($id == NULL) throw new \Exception("No character selected.");
                $character = Character::find($id);
                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$pet) throw new \Exception("An invalid pet was selected.");
                if($pet->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this pet.");
                if(!$character) throw new \Exception("An invalid character was selected.");
                if($character->user_id !== $user->id && !$user->hasPower('edit_inventories'))throw new \Exception("You do not own this character.");

                $pet['chara_id'] = $character->id;
                $pet['attached_at'] = Carbon::now();
                $pet->save();
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * detaches a pet stack.
     *
     */
    public function detachStack($pet)
    {
        DB::beginTransaction();

        try {
                $user = Auth::user();
                if(!$user->hasAlias) throw new \Exception("Your deviantART account must be verified before you can perform this action.");
                if(!$pet) throw new \Exception("An invalid pet was selected.");
                if($pet->user_id != $user->id && !$user->hasPower('edit_inventories')) throw new \Exception("You do not own this pet.");

                $pet['chara_id'] = null;
                $pet->save();
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * edits variant
     */
    public function editVariant($id, $pet)
    {
        DB::beginTransaction();

        try {

                $pet['variant_id'] = $id;
                $pet->save();
            
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Credits an pet to a user.
     *
     * @param  \App\Models\User\User  $sender
     * @param  \App\Models\User\User  $recipient
     * @param  string                 $type 
     * @param  array                  $data
     * @param  \App\Models\Pet\Pet  $pet
     * @param  int                    $quantity
     * @return bool
     */
    public function creditPet($sender, $recipient, $type, $data, $pet, $quantity)
    {
        DB::beginTransaction();

        try {
            for($i = 0; $i < $quantity; $i++) UserPet::create(['user_id' => $recipient->id, 'pet_id' => $pet->id, 'data' => json_encode($data)]);
            if($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, null, $type, $data['data'], $pet->id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Moves an pet stack from one user to another.
     *
     * @param  \App\Models\User\User      $sender
     * @param  \App\Models\User\User      $recipient
     * @param  string                     $type 
     * @param  array                      $data
     * @param  \App\Models\User\UserPet  $pet
     * @return bool
     */
    public function moveStack($sender, $recipient, $type, $data, $stack)
    {
        DB::beginTransaction();

        try {
            $stack->user_id = $recipient->id;
            $stack->save();

            if($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, $stack->id, $type, $data['data'], $stack->pet_id, 1)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Debits an pet from a user.
     *
     * @param  \App\Models\User\User      $user
     * @param  string                     $type 
     * @param  array                      $data
     * @param  \App\Models\Pet\UserPet  $stack
     * @return bool
     */
    public function debitStack($user, $type, $data, $stack)
    {
        DB::beginTransaction();

        try {
            $stack->delete();

            if($type && !$this->createLog($user ? $user->id : null, null, $stack->id, $type, $data['data'], $stack->pet_id, 1)) throw new \Exception("Failed to create log.");

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
    public function createLog($senderId, $recipientId, $stackId, $type, $data, $petId, $quantity)
    {
        return DB::table('user_pets_log')->insert(
            [       
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'stack_id' => $stackId,
                'log' => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type' => $type,
                'data' => $data, // this should be just a string
                'pet_id' => $petId,
                'quantity' => $quantity,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
    }

    /**
     * Transfers pets between a user and showcase.
     *
     * @param  \App\Models\User\User|\App\Models\Showcase\Showcase          $sender
     * @param  \App\Models\User\User|\App\Models\Showcase\Showcase          $recipient
     * @param  \App\Models\User\UserPet|\App\Models\Showcase\ShowcaseStock  $stacks
     * @return bool
     */
    public function sendShowcasePet($sender, $recipient, $stacks)
    {

        DB::beginTransaction();

        try {
            $stack = $stacks;
                $quantity = 1;

                if(!$stack) throw new \Exception("Invalid or no stack selected.");
                if(!$recipient) throw new \Exception("Invalid recipient selected.");
                if(!$sender) throw new \Exception("Invalid sender selected.");

                if($recipient->logType == 'Showcase' && $sender->logType == 'Showcase') throw new \Exception("Cannot transfer pets between ".__('showcase.showcases').".");
                if($recipient->logType == 'Showcase' && !$sender->hasPower('edit_inventories') && !$recipient->is_visible) throw new \Exception("Invalid ".__('showcase.showcases')." selected.");
                if(!$stacks) throw new \Exception("Invalid stack selected.");
                if($sender->logType == 'Showcase' && $quantity <= 0 && $stack->count > 0) $quantity = $stack->count;
                if($quantity <= 0) throw new \Exception("Invalid quantity entered.");

                if(($recipient->logType == 'Showcase' && !$sender->hasPower('edit_inventories') && !Auth::user() == $recipient->user) || ($recipient->logType == 'User' && !Auth::user()->hasPower('edit_inventories') && !Auth::user() == $sender->user)) throw new \Exception("Cannot transfer pets to/from a showcase you don't own.");

                if((!$stack->pet->allow_transfer || isset($stack->data['disallow_transfer'])) && !Auth::user()->hasPower('edit_inventories')) throw new \Exception("One of the selected pets cannot be transferred.");

                if($recipient->logType == 'Showcase' && $stack->count < $quantity) throw new \Exception("Quantity to transfer exceeds pet count."); 

                if($recipient->logType == 'User' && $stack->quantity < $quantity) throw new \Exception("Quantity to transfer exceeds pet count."); 

                if(!$this->showcasePet($sender, $recipient, $stack->data, $stack->pet)) throw new \Exception("Could not transfer pet to ".__('showcase.showcases').".");


                $stack->count -= $quantity;
                if($stack->count == 0) {
                    $stack->delete(); //delete the pet because shenanigans will occur lmao
                }
                $stack->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Credits an pet to a user or showcase.
     *
     * @param  \App\Models\User\User|\App\Models\Showcase\Showcase  $sender
     * @param  \App\Models\User\User|\App\Models\Showcase\Showcase  $recipient
     * @param  string                                                 $type 
     * @param  array                                                  $data
     * @param  \App\Models\Pet\Pet                                  $pet
     * @return bool
     */
    public function showcasePet($sender, $recipient, $data, $pet)
    {
        DB::beginTransaction();

        try {
           
            $encoded_data = \json_encode($data); 
                    $recipient_stack = ShowcaseStock::create(['showcase_id' => $recipient->id,'stock_type' => 'Pet', 'item_id' => $pet->id, 'data' => $encoded_data]);
                $recipient_stack->quantity += 1;
                $recipient_stack->save();
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Credits an pet to a user or showcase.
     *
     * @param  \App\Models\User\User|\App\Models\Showcase\Showcase  $sender
     * @param  \App\Models\User\User|\App\Models\Showcase\Showcase  $recipient
     * @param  string                                                 $type 
     * @param  array                                                  $data
     * @param  \App\Models\Pet\Pet                                  $pet
     * @return bool
     */
    public function removePet($sender, $recipient, $data, $pet)
    {
        DB::beginTransaction();

        try {
            $encoded_data = \json_encode($data); 
            $recipient_stack = UserPet::create(['user_id' => $recipient->id, 'pet_id' => $pet->item_id, 'data' => $encoded_data]);
            $pet->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}