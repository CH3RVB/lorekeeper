<?php
namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterTransfer;
use App\Models\Currency\Currency;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesBid;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class UserSalesService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Sales Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of Sales posts.
    |
    */

    /**********************************************************************************************

        MANAGE

    **********************************************************************************************/

    /**
     * Creates a Sales post.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Sales
     */
    public function createSales($data, $user, $submit = false)
    {
        DB::beginTransaction();

        try {
            if (! $user->hasAlias) {
                throw new \Exception("You need to have a linked social media account before you can perform this action.");
            }

            $data = $this->populateData($data, $user);

            $sales = Sales::create($data);

            // The character identification comes in both the slug field and as character IDs
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }

            } else {
                $characters = [];
            }

            // Process entered character data
            if (isset($data['slug'])) {
                $this->processCharacters($sales, $data, $user);
            }

            if ($sales->is_visible) {
                $this->alertUsers();
            }

            return $this->commitReturn($sales);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a Sales post.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $sales
     *
     * @return bool|Sales
     */
    public function updateSales($sales, $data, $user, $submit = false)
    {
        DB::beginTransaction();

        try {
            if ($sales->user_id != $user->id && ! $user->hasPower('edit_inventories')) {
                throw new \Exception("You do not own the selected sale.");
            }

            $data = $this->populateData($data, $user, $sales);

            if (isset($data['bump']) && $data['is_visible'] == 1 && $data['bump'] == 1) {
                $this->alertUsers();
            }

            // The character identification comes in both the slug field and as character IDs
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }

            } elseif ($data['is_visible'] && ! isset($data['slug'])) {
                throw new \Exception("Must set a sale character.");
            }

            // Remove existing attached characters, then process entered character data
            $sales->characters()->delete();
            if (isset($data['slug'])) {
                $this->processCharacters($sales, $data, $user);
            }

            $sales->update($data);

            return $this->commitReturn($sales);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a Sales post.
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function deleteSales($sales)
    {
        DB::beginTransaction();

        try {

            $sales->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes sales data entered for characters.
     *
     * @param  App\Models\UserSales\UserSales                   $sales
     * @param  array                                    $data
     * @return bool
     */
    private function populateData($data, $user, $sales = null)
    {
        $data['parsed_text'] = parse($data['text']);
        $data['user_id']     = $user->id;
        if (! isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (! isset($data['is_open'])) {
            $data['is_open'] = 0;
        }

        $data['is_admin'] = 0;

        return $data;

    }

    /**
     * Updates the unread Sales flag for all users so that
     * the new Sales notification is displayed.
     *
     * @return bool
     */
    public function alertUsers()
    {

        DB::beginTransaction();

        try {
            $query = User::where(function ($query) {
                //only get the users who want pings
                $query = $query->whereRelation('settings', 'get_user_sale_pings', 1);
            })->get();

            foreach ($query as $user) {
                $user->update(['is_user_sales_unread' => 1]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
