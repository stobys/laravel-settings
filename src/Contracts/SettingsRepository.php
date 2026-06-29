<?php

namespace stobys\Settings\Contracts;

interface SettingsRepository
{
    /**
     * Pobierz wartość ustawienia. Gdy $userId = null – ustawienie globalne.
     */
    public function get(string $key, mixed $default = null, ?int $userId = null): mixed;

    /**
     * Ustaw (lub zaktualizuj) wartość ustawienia.
     */
    public function set(string $key, mixed $value, ?int $userId = null): void;

    /**
     * Sprawdź, czy klucz istnieje.
     */
    public function has(string $key, ?int $userId = null): bool;

    /**
     * Usuń ustawienie.
     */
    public function forget(string $key, ?int $userId = null): void;

    /**
     * Zwróć wszystkie ustawienia jako tablicę ['key' => value].
     * Gdy $userId = null – tylko globalne; gdy podany – globalne + user (user nadpisuje global).
     */
    public function all(?int $userId = null): array;

    /**
     * Wyczyść cache (wymusza przeładowanie z bazy).
     */
    public function clearCache(): void;
}
