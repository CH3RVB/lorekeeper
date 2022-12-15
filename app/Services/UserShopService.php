<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Shop\UserShop;
use App\Models\Shop\UserShopStock;

class UserShopService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | User Shop Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of shops and shop stock.
    |
    */

    /**********************************************************************************************
     
        SHOPS

    **********************************************************************************************/
    
    /**
     * Creates a new shop.
     *
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\UserShop
     */
    public function createShop($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateShopData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $shop = UserShop::create($data);

            if ($image) $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Updates a shop.
     *
     * @param  \App\Models\Shop\UserShop  $shop
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\UserShop
     */
    public function updateShop($shop, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(UserShop::where('name', $data['name'])->where('id', '!=', $shop->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateShopData($data, $shop);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $shop->update($data);

            if ($shop) $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Updates shop stock.
     *
     * @param  \App\Models\Shop\UserShop  $shop
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\UserShop
     */
    public function updateShopStock($shop, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(!$data['stock_type']) throw new \Exception("Please select a stock type.");
            if(!$data['item_id']) throw new \Exception("You must select an item.");

            $shop->stock()->create([
                'shop_id'               => $shop->id,
                'item_id'               => $data['item_id'],
                'currency_id'           => $data['currency_id'],
                'cost'                  => $data['cost'],
                'use_user_bank'         => isset($data['use_user_bank']),
                'use_character_bank'    => isset($data['use_character_bank']),
                'quantity'              => isset($data['is_limited_stock']) ? $data['quantity'] : 0,
                'stock_type'            => $data['stock_type'],
                'is_visible'            => isset($data['is_visible']) ? $data['is_visible'] : 0,
            ]);

            return $this->commitReturn($shop);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates shop stock.
     *
     * @param  \App\Models\Shop\UserShop  $shop
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\UserShop
     */
    public function editShopStock($stock, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(!$data['stock_type']) throw new \Exception("Please select a stock type.");
            if(!$data['item_id']) throw new \Exception("You must select an item.");

            $stock->update([
                'shop_id'               => $stock->shop->id,
                'item_id'               => $data['item_id'],
                'currency_id'           => $data['currency_id'],
                'cost'                  => $data['cost'],
                'quantity'              => isset($data['is_limited_stock']) ? $data['quantity'] : 0,
                'stock_type'            => $data['stock_type'],
                'is_visible'            => isset($data['is_visible']) ? $data['is_visible'] : 0,
            ]);

            return $this->commitReturn($stock);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a shop.
     *
     * @param  array                  $data 
     * @param  \App\Models\Shop\UserShop  $shop
     * @return array
     */
    private function populateShopData($data, $shop = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);
        
        if(isset($data['remove_image']))
        {
            if($shop && $shop->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }
    
    /**
     * Deletes a shop.
     *
     * @param  \App\Models\Shop\UserShop  $shop
     * @return bool
     */
    public function deleteShop($shop)
    {
        DB::beginTransaction();

        try {
            // Delete shop stock
            $shop->stock()->delete();

            if($shop->has_image) $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName); 
            $shop->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts shop order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortShop($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                UserShop::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}