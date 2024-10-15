<?php

namespace App\Console\Commands;

use App\Models\FetchQuest\FetchQuest;
use App\Models\Item\Item;
use Illuminate\Console\Command;

class ChangeFetchItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-fetch-item';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes currently wanted fetch item.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //fetch all fetch quests so we can update them
        $fetches = FetchQuest::all();
        foreach ($fetches as $fetch) {
            //fetch the items
            //if category set, fish through that category
            //if none, pull from all items onsite
            //filter through both and remove everything in the "exception" list as well

            $query = Item::where('allow_transfer', 1)->released();

            if ($fetch->exceptions->count()) {
                foreach ($fetch->exceptions as $exception) {
                    switch ($exception->exception_type) {
                        case 'Item':
                            $query->where('id', '<>', $exception->exception_id);
                            break;
                        case 'ItemCategory':
                            $query->where('item_category_id', '<>', $exception->exception_id);
                            break;
                    }
                }
            }
            if ($fetch->fetch_category) {
                $query->where('item_category_id', $fetch->fetch_category);
            }
            if (!$query->count()) {
                throw new \Exception('There are no items to select from!');
            }

            $items = $query->get();
            //randomly select an item
            $totalWeight = $items->count();
            $roll = mt_rand(0, $totalWeight - 1);
            $result = $items[$roll]->id;

            //if the result = the current item set as the fetch, reroll until it is a new one
            //(for anyone reading, it's possible to get stuck in an infinite loop if not enough items are available)
            //reroll a max number of times before just letting it pick the same item again
            $setting = $fetch->fetch_item;
            $count = 0;
            while ($result == $setting) {
                $roll = mt_rand(0, $totalWeight - 1);
                $result = $items[$roll]->id;
                $count++;
                if($count == 10) {
                    $result = $setting;
                    break;
                }
            }

            //randomize currency max/min if all set. otherwise ignore
            if (isset($fetch->extras['reward_min_min']) && isset($fetch->extras['reward_min_max']) && isset($fetch->extras['reward_max_min']) && isset($fetch->extras['reward_max_max'])) {
                //start with rand the min
                $roll2 = mt_rand($fetch->extras['reward_min_min'], $fetch->extras['reward_min_max']);
                //rand the max
                $roll3 = mt_rand($fetch->extras['reward_max_min'], $fetch->extras['reward_max_max']);
                //set the value.
                $fetch->current_min = $roll2;
                $fetch->current_max = $roll3;
                $fetch->save();

            }

            //set the current requested item as the result
            $fetch->fetch_item = $result;
            $fetch->save();

        }

    }
}
