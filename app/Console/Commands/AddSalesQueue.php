<?php
namespace App\Console\Commands;

use App\Models\User\User;
use App\Services\QueueService;
use DB;
use Illuminate\Console\Command;
use Settings;
use App\Models\Queue\Queue;

class AddSalesQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-sales-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the sales queue for processing';

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
     * @return int
     */
    public function handle()
    {
        if (! DB::table('site_settings')->where('key', 'sales_queue_id')->exists()) {
            $this->info('This will create a new queue for users to submit sales posts to. Once created, you can modify this queue as desired.');
            $this->info('It will also create a site setting to store this queue\'s ID in and set it to the created queues\'.');

            $data = [
                'queue_category_id' => null,
                'name'              => 'Character Sales Submissions',
                'summary'           => 'Submit sales posts for approval and editing.',
                'hide_submissions'  => 0,
                'is_active'         => 1,
                'queue_type'        => 'chara_sale',
            ];

            $service = new QueueService;

            if (! $queue = $service->createQueue($data, User::find(Settings::get('admin_user')))) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    $this->error($error);
                }

            } else {
                $this->info("Added:   Character Sales Submission Queue");
            }

            $this->line('Adding site settings...');

            if (isset($queue) && $queue) {
                if (! DB::table('site_settings')->where('key', 'sales_queue_id')->exists()) {
                    DB::table('site_settings')->insert([
                        [
                            'key'         => 'sales_queue_id',
                            'value'       => $queue->id,
                            'description' => 'ID of the queue to use for sales submissions.',
                        ],

                    ]);
                    $this->info("Added:   sales_queue_id");
                } else {
                    $this->line("Skipped: sales_queue_id");
                }

            }

        } else {
            $this->line('There is already a queue ID set. Please modify this via site settings.');
            $this->line('Skipping...');
        }
    }
}
