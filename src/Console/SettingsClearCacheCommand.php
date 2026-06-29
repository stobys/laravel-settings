<?php

namespace stobys\Settings\Console;

use Illuminate\Console\Command;
use stobys\Settings\Contracts\SettingsRepository;

class SettingsClearCacheCommand extends Command
{
    protected $signature = 'settings:clear-cache
                            {--rebuild : Wyczyść i od razu przebuduj cache z bazy danych}';

    protected $description = 'Wyczyść plikowy cache ustawień (settings)';

    public function handle(SettingsRepository $repository): int
    {
        $repository->clearCache();
        $this->info('Cache ustawień został wyczyszczony.');

        if ($this->option('rebuild')) {
            // Wymusi rebuild przy następnym odczycie automatycznie,
            // ale możemy go też wywołać ręcznie przez odczyt all()
            $repository->all(); // <-- rebuild
            $this->info('Cache został przebudowany z bazy danych.');
        }

        return self::SUCCESS;
    }
}
