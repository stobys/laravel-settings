<?php

namespace SylveK\LaravelSettings\Console;

use Illuminate\Console\Command;

class SettingGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setting:get
                            {key : Setting key}
                            {user? : User ID or username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a setting value.';

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
        $headers = ['Key', 'Value', 'User ID'];
        $data = [
            $this -> argument('key'),
            settings() -> setUser($this->argument('user')) -> get($this->argument('key')),
            $this -> argument('user'),
        ];

        $this -> table($headers, [$data]);
    }

}
