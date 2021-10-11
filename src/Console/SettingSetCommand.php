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
                            {user? : User ID or username, null for global setting}';

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
        $key = config('db_field_key', 'setting_key');
        $val = config('db_field_value', 'setting_value');

        $headers = ['Setting', 'Value', 'User ID'];
        $data = [
            $key   => $this->argument('key'),
            $val     => $this->argument('value'),
            'user_id'   => $this->argument('user'),
        ];

        settings() -> set($data[$key], $data[$val], $data['user_id']);

        $this -> table($headers, [$data]);
    }
}
