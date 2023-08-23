<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Models\User\UserGear;
use App\Models\User\UserWeapon;

class AddClaymoreSlots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-claymore-slots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate in the slots for user gear/weapons. (In the case of there being existing gear/weapons before slots were added.) SHOULD ONLY RUN THIS ONCE. Multiple runs will overwrite user slot progress.';

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
        if($this->confirm('You should only run this command once. User claymore slots will be overwritten if you run this a second time. Are you sure you want to run it?')) {
            $this->line("Adding slots...");
            //gear
            $gears = UserGear::all();
            foreach($gears as $gear)
            {
            $gear->slots = $gear->gear->slots;
            $gear->save();
            }
            $this->line("Migrated gears\n");

            //weapon
            $weapons = UserWeapon::all();
            foreach($weapons as $weapon)
            {
            $weapon->slots = $weapon->weapon->slots;
            $weapon->save();
            }
            $this->line("Migrated weapons\n");

            $this->line("Done!\n");
        }
    }
}
