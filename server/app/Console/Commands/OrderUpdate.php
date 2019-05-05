<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User_rights;

class OrderUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'dddddd';

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
        //
        $this->reset();
    }
    /**
     *
     *
     *
     * */
    private function reset()
    {
        $res = User_rights::where('pay_status', 0)->get();
        if (!empty($res)) {
            foreach ($res as &$v) {
                if (time() - strtotime($v['created_at']) > (10*60)) {
                    User_rights::where('id', $v['id'])->update(['pay_status' => 3]);
                }
            }
        }
      
    }
}
