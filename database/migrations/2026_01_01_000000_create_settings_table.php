<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
	protected $config = [];

    public function __construct()
    {
        $this->config = config('settings', []);
    }
	
    public function up(): void
    {
		$tableName = Arr::get($this->config, 'db_table', 'settings');
		
		if (empty($tableName)) {
            throw new \Exception('Error: config/settings.php not found and defaults could not be merged. Please publish the package configuration before proceeding.');
        }
		
        $table   = Config::get('settings.table', 'settings');
        $colKey  = Config::get('settings.columns.key', 'key');
        $colVal  = Config::get('settings.columns.value', 'value');
        $colUser = Config::get('settings.columns.user_id', 'user_id');
        $colType = Config::get('settings.columns_type', 'type');

        Schema::create($table, function (Blueprint $table) use ($colKey, $colVal, $colUser, $colType) {
            $table->id();
            $table->string($colKey, 128);
            $table->text($colVal)->nullable();

            // typ oryginalnej wartości PHP (int, bool, float, array, null, string)
            $table->string($colType, 10)->default('string');

            // null = ustawienie globalne, int = ustawienie per-użytkownik
            $table->foreignId($colUser)->nullable()->constrained()->index();

            $table -> dateTime('created_at') -> nullable();
            $table -> dateTime('updated_at') -> nullable() -> useCurrent();
            $table -> dateTime('deleted_at') -> nullable();

            // unikalność klucza w zakresie użytkownika (null traktowane jako osobna wartość)
            $table->unique([$colKey, $colUser]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('settings.table', 'settings'));
    }
};
