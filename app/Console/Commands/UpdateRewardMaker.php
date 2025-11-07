<?php

namespace App\Console\Commands;

use App\Models\ObjectReward;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class UpdateRewardMaker extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-reward-maker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update reward maker for develop';

    /**
     * Execute the console command.
     */
    public function handle() {
        if (!Schema::hasTable('prompt_rewards')) {
            $this->info('Already ran conversions.');

            return;
        }

        if ($this->confirm('Have you migrated after installation?')) {
            $this->info('If you have edited the reward maker to include custom reward types and other object support, ensure that they have been added to the UpdateRewardMaker command file or errors and data loss may occur.');
            if ($this->confirm('Have you either edited the UpdateRewardMaker file to include your edits, or not edited the reward maker at all?')) {
                $this->info('Converting rewards.');
                $rewards = DB::table('prompt_rewards')->get();

                foreach ($rewards as $promptreward) {
                    $rewardmodel = getAssetModelString(strtolower($promptreward->rewardable_type));

                    $newreward = ObjectReward::create([
                        'object_id'       => $promptreward->prompt_id,
                        'object_type'     => 'App\Models\Prompt\Prompt',
                        'rewardable_type' => $rewardmodel,
                        'rewardable_id'   => $promptreward->rewardable_id,
                        'quantity'        => $promptreward->quantity,
                        'recipient_type'  => 'User',
                        'reward_key'      => 'objectRewards',
                    ]);

                    if (!$newreward) {
                        $this->error('Error. Skipping prompt reward for prompt: '.$promptreward->prompt->name);
                    }
                }
                $this->info('Dropping prompt rewards');
                Schema::dropIfExists('prompt_rewards');

                $this->info('Converting any existing rewards...');

                $objrewards = ObjectReward::all();

                foreach ($objrewards as $reward) {
                    switch ($reward->object_type) {
                        case 'Questline':
                            $objmodel = 'App\Models\Questline\Questline';
                            break;
                        case 'Prompt':
                            $objmodel = 'App\Models\Prompt\Prompt';
                            break;
                    }

                    switch ($reward->recipient_type) {
                        case 'User':
                            $key = 'objectRewards';
                            break;
                        case 'Character':
                            $key = 'objectCharacterRewards';
                            break;
                    }

                    $reward->update([
                        'rewardable_type' => getAssetModelString(strtolower($reward->rewardable_type)),
                        'object_type'     => $objmodel,
                        'reward_key'      => $key,
                    ]);
                }

                $this->info('Converted rewards successfully. Happy Lorekeeping! :)');
            } else {
                $this->error('Please edit the file to ensure the conversion goes smoothly.');

                return;
            }
        } else {
            $this->info('Migrating DB. After this is complete, run this command again and confirm that you have to continue.');
            $this->call('migrate');
            $this->info('Migrations complete. Please run the command again to continue.');
        }
    }
}
