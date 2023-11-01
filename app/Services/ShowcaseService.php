<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Settings;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseStock;

class ShowcaseService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | User Showcase Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of showcases and showcase stock.
    |
    */

    /**********************************************************************************************
     
        SHOWCASES
    **********************************************************************************************/

    /**
     * Creates a new showcase.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Showcase\Showcase
     */
    public function createShowcase($data, $user)
    {
        DB::beginTransaction();

        try {
            //check for showcase limit, if there is one
            if (Settings::get('user_showcase_limit') != 0) {
                if (Showcase::where('user_id', $user->id)->count() >= Settings::get('user_showcase_limit')) {
                    throw new \Exception('You have already created the maximum number of ' . __('showcase.showcases') . '.');
                }
            }

            $data['user_id'] = $user->id;
            $data = $this->populateShowcaseData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $showcase = Showcase::create($data);

            if ($image) {
                $this->handleImage($image, $showcase->showcaseImagePath, $showcase->showcaseImageFileName);
            }

            return $this->commitReturn($showcase);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a showcase.
     *
     * @param  \App\Models\Showcase\Showcase  $showcase
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Showcase\Showcase
     */
    public function updateShowcase($showcase, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if (
                Showcase::where('name', $data['name'])
                    ->where('id', '!=', $showcase->id)
                    ->exists()
            ) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateShowcaseData($data, $showcase);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $showcase->update($data);

            if ($showcase) {
                $this->handleImage($image, $showcase->showcaseImagePath, $showcase->showcaseImageFileName);
            }

            return $this->commitReturn($showcase);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates showcase stock.
     *
     * @param  \App\Models\Showcase\Showcase  $showcase
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Showcase\Showcase
     */
    public function editShowcaseStock($stock, $data, $user)
    {
        DB::beginTransaction();

        try {
            $stock->update([
                'is_visible' => isset($data['is_visible']) ? $data['is_visible'] : 0,
            ]);

            return $this->commitReturn($stock);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a showcase.
     *
     * @param  array                  $data
     * @param  \App\Models\Showcase\Showcase  $showcase
     * @return array
     */
    private function populateShowcaseData($data, $showcase = null)
    {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }
        $data['is_active'] = isset($data['is_active']);

        if (isset($data['remove_image'])) {
            if ($showcase && $showcase->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($showcase->showcaseImagePath, $showcase->showcaseImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Deletes a showcase.
     *
     * @param  \App\Models\Showcase\Showcase  $showcase
     * @return bool
     */
    public function deleteShowcase($showcase)
    {
        DB::beginTransaction();

        try {
            if ($showcase->stock->where('quantity', '>', 0)->count()) {
                throw new \Exception('This ' . __('showcase.showcase') . ' currently has items stocked. Please remove them and try again.');
            }
            //delete the 0 stock items or the showcase cannot be deleted
            $showcase->stock()->delete();

            if ($showcase->has_image) {
                $this->deleteImage($showcase->showcaseImagePath, $showcase->showcaseImageFileName);
            }
            $showcase->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts showcase order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortShowcase($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Showcase::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * quick edit stock
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @param  bool                   $isClaim
     * @return mixed
     */
    public function quickstockStock($data, $showcase, $user)
    {
        DB::beginTransaction();
        try {
            if (isset($data['stock_id'])) {
                foreach ($data['stock_id'] as $key => $itemId) {
                    $stock = ShowcaseStock::find($itemId);
                    //update the data of the stocks
                    $stock->update([
                        'is_visible' => isset($data['is_visible'][$key]) ? $data['is_visible'][$key] : 0,
                    ]);
                    //transfer them if qty selected
                    if (isset($data['quantity'][$key]) && $data['quantity'][$key] > 0) {
                        //check stock type
                        if ($stock->stock_type == 'Item') {
                            if (!(new InventoryManager())->sendShowcase($showcase, $showcase->user, $stock, $data['quantity'][$key])) {
                                throw new \Exception('Could not transfer item to user.');
                            }
                        } elseif ($stock->stock_type == 'Pet') {
                            if (!(new PetManager())->sendShowcase($showcase, $showcase->user, $stock, $data['quantity'][$key])) {
                                throw new \Exception('Could not transfer pet to user.');
                            }
                        }
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
