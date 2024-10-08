<?php

namespace App\Console\Commands;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\CharacterItem;
use App\Models\Item\Item;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\InventoryManager;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-expired-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any expired user_items, and if so, deletes them.';

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
        //get all items with expiries
        $items = Item::whereNotNull('expiry_date')->whereNull('expiry_number')->whereNull('expiry_interval')->get();
        $admin = User::find(Settings::get('admin_user'));

        $this->info('Checking global items to delete.');
        $this->info($items->count() . ' items being checked...');

        //global items first
        foreach ($items as $item) {
            //if expired, time to roll
            if (Carbon::parse($item->expiry_date)->isPast()) {
                //get all the items
                $expired = UserItem::where('item_id', $item->id)
                    ->where('count', '>', 0)
                    ->get();

                foreach ($expired as $expired) {
                    $this->deleteItems($expired, $admin);
                }

                //character items too ofc i would never forget this pls dont look at the commits
                $expiredc = CharacterItem::where('item_id', $item->id)
                    ->where('count', '>', 0)
                    ->get();

                foreach ($expiredc as $cExpired) {
                    $this->deleteItems($cExpired, $admin, true);
                }
            }
        }

        //then personal ones
        $personalitems = Item::whereNotNull('expiry_number')->whereNotNull('expiry_interval')->whereNull('expiry_date')->get();

        $this->info('Checking personal items to delete.');
        $this->info($personalitems->count() . ' items being checked...');

        foreach ($personalitems as $pitem) {
            //get all the items
            $useritems = UserItem::where('item_id', $pitem->id)
                ->where('count', '>', 0)
                ->get();

            foreach ($useritems as $uitem) {
                //get the original date...
                $obtained = Carbon::parse($uitem->created_at);

                //then the date after the original date has the item's intervals tacked on...
                $expirydate = $obtained->add($pitem->expiry_number, $pitem->expiry_interval);
                //...now check all the gotten items haven't been around for longer than the calculated expiry date...
                if ($expirydate->isPast()) {
                    $this->deleteItems($uitem, $admin);
                }
            }

            //get all the items
            $charitems = CharacterItem::where('item_id', $pitem->id)
                ->where('count', '>', 0)
                ->get();

            foreach ($charitems as $citem) {
                //get the original date...
                $cobtained = Carbon::parse($citem->created_at);

                //then the date after the original date has the item's intervals tacked on...
                $cexpirydate = $cobtained->add($pitem->expiry_number, $pitem->expiry_interval);
                //...now check all the gotten items haven't been around for longer than the calculated expiry date...
                if ($cexpirydate->isPast()) {
                    $this->deleteItems($citem, $admin, true);
                }
            }
        }

        $this->info('All expired items checked!');
    }

    /**
     * Delete the items (with the actual LK inventory manager)
     */
    public function deleteItems($expired, $admin, $isCharacter = 0)
    {
        if ($isCharacter) {
            $owner = $expired->character;
        } else {
            $owner = $expired->user;
        }

        //store the count for later when we send the notif, otherwise it just says x0 was deleted
        $total =  $expired->count;

        //take the items...
        if (!(new InventoryManager)->debitStack($owner, 'Item Expired', ['data' => 'Item automatically expired'], $expired, $total)) {
            throw new \Exception('An error occurred while trying to delete the items.');
        }

        //probably a good idea to send out notifs to everyone who had their items taken, so...
        if ($isCharacter) {
            Notifications::create('CHARACTER_ITEM_REMOVAL', $expired->character->user, [
                'item_name' => $expired->item->name,
                'item_quantity' => $total,
                'sender_url' =>  $admin->url,
                'sender_name' =>  $admin->name,
                'character_name' => $expired->character->fullName,
                'character_slug' => $expired->character->slug
            ]);
        } else {
            Notifications::create('ITEM_REMOVAL', $owner, [
                'item_name' => $expired->item->name,
                'item_quantity' => $total,
                'sender_url' => $admin->url,
                'sender_name' => $admin->name,
            ]);
        }

    }
}
