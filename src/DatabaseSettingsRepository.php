<?php

namespace stobys\LaravelSettings;

use Illuminate\Support\Facades\DB;
use stobys\LaravelSettings\Cache\FileSettingsCache;
use stobys\LaravelSettings\Cache\ValueCaster;
use stobys\LaravelSettings\Contracts\SettingsRepository;

class DatabaseSettingsRepository implements SettingsRepository
{
    private string $table;
    private string $colKey;
    private string $colValue;
    private string $colUserId;
    private string $colType;
    private bool $castTypes;
    private bool $cacheEnabled;

    public function __construct(
        private readonly FileSettingsCache $fileCache,
        private readonly array $config
    ) {
        $this->table       = $config['table'];
        $this->colKey      = $config['columns']['key'];
        $this->colValue    = $config['columns']['value'];
        $this->colUserId   = $config['columns']['user_id'];
        $this->colType     = $config['columns_type'];
        $this->castTypes   = $config['cast_types'];
        $this->cacheEnabled = $config['cache']['enabled'];
    }

    // -------------------------------------------------------------------------
    // Publiczne API
    // -------------------------------------------------------------------------

    public function get(string $key, mixed $default = null, ?int $userId = null): mixed
    {
        if ($this->cacheEnabled) {
            // Jeśli cache istnieje – użyj go
            if ($this->fileCache->exists()) {
                $cached = $this->fileCache->has($key, $userId);
                return $cached ? $this->fileCache->get($key, $userId) : $default;
            }

            // Cache nie istnieje – załaduj wszystko z bazy i zbuduj plik
            $this->rebuildCache();
            return $this->fileCache->has($key, $userId)
                ? $this->fileCache->get($key, $userId)
                : $default;
        }

        // Cache wyłączony – bezpośrednio z bazy
        $row = $this->findRow($key, $userId);

        if ($row === null && $userId !== null) {
            // Fallback do ustawienia globalnego
            $row = $this->findRow($key, null);
        }

        if ($row === null) {
            return $default;
        }

        return $this->castTypes
            ? ValueCaster::deserialize($row->{$this->colValue}, $row->{$this->colType} ?? 'string')
            : $row->{$this->colValue};
    }

    public function set(string $key, mixed $value, ?int $userId = null): void
    {
        ['value' => $raw, 'type' => $type] = $this->castTypes
            ? ValueCaster::serialize($value)
            : ['value' => (string) $value, 'type' => 'string'];

        $attributes = [
            $this->colValue  => $raw,
            $this->colType   => $type,
            'updated_at'     => now(),
        ];

        $uniqueKey = [
            $this->colKey    => $key,
            $this->colUserId => $userId,
        ];

        $exists = DB::table($this->table)->where($uniqueKey)->exists();

        if ($exists) {
            DB::table($this->table)->where($uniqueKey)->update($attributes);
        } else {
            DB::table($this->table)->insert(array_merge($uniqueKey, $attributes, ['created_at' => now()]));
        }

        // Zaktualizuj cache z już zdezserializowaną wartością
        if ($this->cacheEnabled) {
            $this->fileCache->put($key, $value, $userId);
        }
    }

    public function has(string $key, ?int $userId = null): bool
    {
        if ($this->cacheEnabled && $this->fileCache->exists()) {
            return $this->fileCache->has($key, $userId);
        }

        return $this->findRow($key, $userId) !== null;
    }

    public function forget(string $key, ?int $userId = null): void
    {
        DB::table($this->table)
            ->where($this->colKey, $key)
            ->where($this->colUserId, $userId) // null = IS NULL w MySQL z ->where()
            ->delete();

        if ($this->cacheEnabled) {
            $this->fileCache->remove($key, $userId);
        }
    }

    public function all(?int $userId = null): array
    {
        if ($this->cacheEnabled) {
            if (!$this->fileCache->exists()) {
                $this->rebuildCache();
            }
            return $this->fileCache->all($userId);
        }

        return $this->fetchAllFromDb($userId);
    }

    public function clearCache(): void
    {
        $this->fileCache->invalidate();
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function findRow(string $key, ?int $userId): ?object
    {
        return DB::table($this->table)
            ->where($this->colKey, $key)
            ->where($this->colUserId, $userId)
            ->first();
    }

    /**
     * Pobierz wszystkie ustawienia z bazy i zwróć jako ['key' => value].
     * Dla $userId != null: globalne + per-user (user nadpisuje globalne).
     */
    private function fetchAllFromDb(?int $userId): array
    {
        $query = DB::table($this->table)
            ->whereNull($this->colUserId);

        if ($userId !== null) {
            $query = DB::table($this->table)
                ->where(function ($q) use ($userId) {
                    $q->whereNull($this->colUserId)
                      ->orWhere($this->colUserId, $userId);
                })
                // Globalne pierwsza, user-specific nadpisuje przez kolekcję
                ->orderByRaw("CASE WHEN {$this->colUserId} IS NULL THEN 0 ELSE 1 END");
        }

        $rows   = $query->get();
        $result = [];

        foreach ($rows as $row) {
            $value = $this->castTypes
                ? ValueCaster::deserialize($row->{$this->colValue}, $row->{$this->colType} ?? 'string')
                : $row->{$this->colValue};

            $result[$row->{$this->colKey}] = $value;
        }

        return $result;
    }

    /**
     * Pełny rebuild cache z bazy danych.
     * Ładuje wszystkie wiersze i buduje strukturę global / users.
     */
    private function rebuildCache(): void
    {
        $rows = DB::table($this->table)->get();

        $global = [];
        $users  = [];

        foreach ($rows as $row) {
            $value = $this->castTypes
                ? ValueCaster::deserialize($row->{$this->colValue}, $row->{$this->colType} ?? 'string')
                : $row->{$this->colValue};

            if ($row->{$this->colUserId} === null) {
                $global[$row->{$this->colKey}] = $value;
            } else {
                $uid = (int) $row->{$this->colUserId};
                $users[$uid][$row->{$this->colKey}] = $value;
            }
        }

        $this->fileCache->rebuild($global, $users);
    }
}
