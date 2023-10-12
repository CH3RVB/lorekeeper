<?php namespace App\Services\Item;

use App\Services\Service;

use DB;

use App\Services\InventoryManager;

use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Models\Shop\Shop;

class MultiUseService extends Service
{

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [
            
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        $multiuseData = [];
        $multiuseData['uses'] = isset($tag->data['uses']) ? $tag->data['uses'] : null;
        $multiuseData['infinite'] = isset($tag->data['infinite']) ? $tag->data['infinite'] : null;

        return $multiuseData;

    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['infinite'])) $data['infinite'] = 0;

            $multiuse['uses'] = $data['uses'];
            $multiuse['infinite'] = $data['infinite'];
            $tag->update(['data' => json_encode($multiuse)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}