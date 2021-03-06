<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $tableName = config('settings.db_table');

        if (empty($tableName)) {
            throw new \Exception('Error: config/settings.php not found and defaults could not be merged. Please publish the package configuration before proceeding.');
        }

		Schema::create($tableName, function(Blueprint $table)
		{
            $table -> bigIncrements('id');
            $table -> bigInteger('user_id') -> nullable();

            $table -> string('setting', 100);
            $table -> text('value') -> nullable();
            $table -> text('description') -> nullable();

            $table -> dateTime('created_at') -> nullable();
            $table -> dateTime('updated_at') -> nullable() -> useCurrent();
            $table -> dateTime('deleted_at') -> nullable();

			$table -> unique(['setting', 'user_id']);

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$tableName = config('settings.db_table');

        if (empty($tableName)) {
			throw new \Exception('Error: config/settings.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the table manually.');
        }

		Schema::drop($tableName);
	}

}
