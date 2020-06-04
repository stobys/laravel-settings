<?php

namespace SylveK\LaravelSettings\Console;

use Illuminate\Console\Command;

class SettingSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setting:set
                            {key : Setting key}
                            {value : Setting value}
                            {user? : User ID or username, null for global setting}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an int / string setting.';

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
        settings() -> setUser($this->argument('user')) -> set($this->argument('key'), $this->argument('value'));

        $this -> info('Setting added. Succ.');
    }
}
