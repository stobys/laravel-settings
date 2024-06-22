<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;

use App\Models\User;

return new class extends Migration
{
    protected $config = [];

    public function __construct()
    {
        $this->config = config('settings', []);
    }

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $tableName = Arr::get($this->config, 'db_table', 'settings');

        if (empty($tableName)) {
            throw new \Exception('Error: config/settings.php not found and defaults could not be merged. Please publish the package configuration before proceeding.');
        }

		Schema::create($tableName, function(Blueprint $table)
		{
            $key = Arr::get($this->config, 'db_field_key', 'setting_key');
            $val = Arr::get($this->config, 'db_field_value', 'setting_value');

            $table -> id();
            $table -> foreignIdFor(User::class) -> nullable() -> constrained();

            $table -> string($key, 100);
            $table -> text($val) -> nullable();
            $table -> text('description') -> nullable();

            $table -> dateTime('created_at') -> nullable();
            $table -> dateTime('updated_at') -> nullable() -> useCurrent();
            $table -> dateTime('deleted_at') -> nullable();

			$table -> unique([$key, 'user_id']);

		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = Arr::get($this->config, 'db_table', 'settings');

        if (empty($tableName)) {
            throw new \Exception('Error: config/settings.php not found and defaults could not be merged. Please publish the package configuration before proceeding.');
        }

        Schema::dropIfExists($tableName);
    }

};
