<?php
namespace App\Services\Shops;

use App\Services\Service;
use DB;

class ResellService extends Service
{

    /**
     * Retrieves any data that should be used in the shop type editing form on the admin side
     *
     * @return array
     */
    public function getEditData($shop)
    {
        //return only if data exists
        if ($shop->alt_data) {
            //get the random test value
            $testbase = mt_rand(20, 50);
            //get the multiplier
            $testmultiplier = $shop->payMultiplier();
            //test payment amount
            $payment = $shop->currencyPayment($testbase, $testmultiplier);
            //stock multiplier
            $stock_multiplier = $shop->stockMultiplier();
            //get the value after we do the cursed math of adding/subtracting from testbase
            $stockbase = $testbase + $payment;
            //test stock amount
            $stockprice = $shop->currencyPayment($testbase + $payment, $stock_multiplier);
        }

        return $shop->alt_data ? [
            'testbase'         => $testbase,
            'testmultiplier'   => $testmultiplier,
            'payment'          => $payment,
            'stockbase'        => $stockbase,
            'stock_multiplier' => $stock_multiplier,
            'stockprice'       => $stockprice,
        ] : [];
    }

    /**
     * Retrieves any data that should be used in the shop type on the user side
     *
     * @return array
     */
    public function getActData($shop)
    {
        return [
        ];
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
        if (! isset($data['alt_min_grant']) || ! isset($data['alt_max_grant'])) {
            throw new \Exception("Can't make a resell shop with missing grant values.");
        }

        //ignore values if not making a stock
        if (isset($data['alt_makes_stock'])) {
            if (! isset($data['alt_stock_price_min']) || ! isset($data['alt_stock_price_max'])) {
                throw new \Exception("Can't make a resell shop with missing stock cost values.");
            }

            if ($data['alt_min_grant'] > $data['alt_max_grant'] || $data['alt_stock_price_min'] > $data['alt_stock_price_max']) {
                throw new \Exception("A minimum should not be larger than the maximum.");
            }
        }

        return [
            'alt_min_grant'       => isset($data['alt_min_grant']) ? $data['alt_min_grant'] : null,
            'alt_max_grant'       => isset($data['alt_max_grant']) ? $data['alt_max_grant'] : null,
            'alt_stock_price_min' => isset($data['alt_stock_price_min']) ? $data['alt_stock_price_min'] : null,
            'alt_stock_price_max' => isset($data['alt_stock_price_max']) ? $data['alt_stock_price_max'] : null,
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
    public function submit($shop, $data, $user)
    {
        DB::beginTransaction();

        try {

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
