<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class AddMissingNickname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:nickname';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add user missing nickname';

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
        $users = User::whereNull('nickname')->get();
        if(isset($users[0])) {
            foreach($users as $user) {
                User::where('id',$user->id)->update(['nickname' => $user->email]);
            }
        }
    }
}
