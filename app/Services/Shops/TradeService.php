<?php
namespace App\Services\Shops;

use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopLog;
use App\Models\User\UserItem;
use App\Services\InventoryManager;
use App\Services\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradeService extends Service
{

    /**
     * Retrieves any data that should be used in the shop type editing form on the admin side
     *
     * @return array
     */
    public function getEditData($shop)
    {

        return [
        ];
    }

    /**
     * Retrieves any data that should be used in the shop type on the user side
     *
     * @return array
     */
    public function getActData($shop)
    {
        $user = Auth::user();
        return [
            'tradeInventory'   => $user ? $this->getItemOptions($shop) : null,
            'categories'  => $user ? ItemCategory::orderBy('sort', 'DESC')->get() : null,
            'item_filter' => $user ? Item::orderBy('name')->get()->keyBy('id') : null,
        ];
    }

    /**
     * Retrieves any data that should be used in the shop type on the user side
     *
     * @return array
     */
    public function getItemOptions($shop)
    {
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id);

        //filter by applicable items...
        if (isset($shop->alt_data['alt_category']) && $shop->alt_data['alt_category'] != 'all') {
            $inventory = $inventory->whereRelation('item', 'item_category_id', $shop->alt_data['alt_category'])->get()->sortBy('item.name');
        } else {
            $inventory = $inventory->get()->sortBy('item.name');
        }
        return $inventory;
    }


    /**
     * Retrieves any data that should be used in the shop type on the user side
     *
     * @return array
     */
    public function getShopData($shop)
    {
        return [
        ];
    }

    /**
     * Processes the data attribute of the shop and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getData($data)
    {
        return $data;
    }

    /**
     * Processes the data attribute of the shop and returns it in the preferred format.
     *
     * @param  object  $shop
     * @param  array   $data
     * @return bool
     */
    public function updateData($shop, $data)
    {
        if (! isset($data['alt_amount'])) {
            throw new \Exception("Can't make a trade-in shop with no trade amount.");
        }
        if ($data['alt_amount'] <= 0) {
            throw new \Exception("Trade amount needs to be 1 at minimum.");
        }

        return [
            'alt_amount' => $data['alt_amount'],
        ];
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function handleAltStock($shop, $data, $user, $shopStock)
    {
        DB::beginTransaction();

        try {

            $quantity = ceil($data['quantity']);
            if (isset($shop->alt_data['alt_cooldown'])) {
                if ($user->altShopCooldown($shop)) {
                    throw new \Exception("You can't make another trade right now.");
                }
            }

            //validate the trade
            if (! isset($data['stack_quantity'])) {
                throw new \Exception('Please select the items to turn in.');
            }

            if (array_sum($data['stack_quantity']) > $shop->alt_data['alt_amount']) {
                throw new \Exception('Selected too many items... You should select exactly ' . $shop->alt_data['alt_amount'] . '.');
            }

            //remove the items
            //cache the values rly quickly for later, we're deleting these guys so uh... yeah.
            $baseStockCost       = mergeAssetsArrays(createAssetsArray(true), createAssetsArray());
            $userCostAssets      = createAssetsArray();
            $characterCostAssets = createAssetsArray(true);
            $userstacks          = [];
            foreach ($data['stack_id'] as $stackId) {
                $stack        = UserItem::find($stackId);
                $userstacks[] = ['stack' => $stack, 'quantity' => $data['stack_quantity'][$stackId]];

                addAsset($userCostAssets, $stack->item, -$data['stack_quantity'][$stackId]);
                if (! (new InventoryManager)->debitStack($stack->user, 'Turned in to ' . $shop->name, ['data' => ''], $stack, $data['stack_quantity'][$stackId])) {
                    throw new \Exception('Failed to remove item');
                }

            }
            // Add a purchase log
            $shopLog = ShopLog::create([
                'shop_id'      => $shop->id,
                'character_id' => null,
                'user_id'      => $user->id,
                'cost'         => [
                    'base'      => getDataReadyAssets($baseStockCost),
                    'user'      => getDataReadyAssets($userCostAssets),
                    'character' => getDataReadyAssets($characterCostAssets),
                    'coupon'    => null,
                ],
                'stock_type'   => $shopStock->stock_type,
                'item_id'      => $shopStock->item_id,
                'quantity'     => $quantity,
            ]);

            //credit the user the swapped item
            $assets = createAssetsArray();
            addAsset($assets, $shopStock->item, $quantity);

            if (! fillUserAssets($assets, null, $user, 'Shop Trade', [
                'data'  => $shopLog->altData,
                'notes' => 'Traded for ' . format_date($shopLog->created_at),
            ] + ($shopStock->disallow_transfer ? ['disallow_transfer' => true] : []))) {
                throw new \Exception('Failed to trade for item.');
            }

            if (isset($shop->alt_data['alt_makes_stock'])) {
                //make each turned in item become a new stock...
                foreach ($userstacks as $newstock) {
                    $shop->stock()->create([
                        'shop_id'                  => $shop->id,
                        'item_id'                  => $newstock['stack']->item_id,
                        'use_user_bank'            => 1,
                        'use_character_bank'       => 0,
                        'is_limited_stock'         => 1,
                        'quantity'                 => $newstock['quantity'],
                        'purchase_limit'           => $shop->alt_data['alt_purchase_limit'],
                        'purchase_limit_timeframe' => $shop->alt_data['alt_purchase_limit_timeframe'],
                        'disallow_transfer'        => $shop->alt_data['alt_disallow_transfer'],
                        'is_fto'                   => $shop->alt_data['alt_is_fto'],
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
