<?php

namespace stobys\Settings;

use stobys\Settings\Contracts\SettingsRepository;

/**
 * Główny punkt wejścia do pakietu.
 *
 * Użycie:
 *   Settings::set('theme', 'dark');           // globalne
 *   Settings::for(1)->set('theme', 'light');  // per-user
 *   Settings::get('theme');                   // globalne
 *   Settings::for(1)->get('theme');           // per-user (fallback do globalnego)
 *   Settings::for(auth()->id())->all();       // wszystkie dla zalogowanego usera
 */
class SettingsManager
{
    private ?int $userId = null;

    public function __construct(private readonly SettingsRepository $repository) {}

    // -------------------------------------------------------------------------
    // Kontekst użytkownika
    // -------------------------------------------------------------------------

    /**
     * Zwraca nową instancję z ustawionym userId.
     * Oryginalna instancja pozostaje niezmieniona (immutable context).
     */
    public function for(int|string|null $userId): static
    {
        $clone         = clone $this;
        $clone->userId = $userId !== null ? (int) $userId : null;
        return $clone;
    }

    /**
     * Skrót: pobierz ustawienia zalogowanego użytkownika.
     */
    public function forCurrentUser(): static
    {
        return $this->for(auth()->id());
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->repository->get($key, $default, $this->userId);
    }

    public function set(string $key, mixed $value): void
    {
        $this->repository->set($key, $value, $this->userId);
    }

    /**
     * Ustaw wiele ustawień naraz.
     *
     * @param array<string, mixed> $settings
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->repository->set($key, $value, $this->userId);
        }
    }

    public function has(string $key): bool
    {
        return $this->repository->has($key, $this->userId);
    }

    public function forget(string $key): void
    {
        $this->repository->forget($key, $this->userId);
    }

    /**
     * Zwraca wszystkie ustawienia dla aktualnego kontekstu.
     * Jeśli ustawiony userId – globalne + per-user (user nadpisuje).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->repository->all($this->userId);
    }

    // -------------------------------------------------------------------------
    // Cache
    // -------------------------------------------------------------------------

    public function clearCache(): void
    {
        $this->repository->clearCache();
    }

    /**
     * Ustaw wartość tylko jeśli klucz jeszcze nie istnieje.
     */
    public function setDefault(string $key, mixed $value): void
    {
        if (!$this->has($key)) {
            $this->set($key, $value);
        }
    }
}
