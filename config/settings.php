<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tabela w bazie danych
    |--------------------------------------------------------------------------
    | Nazwa tabeli, w której przechowywane są ustawienia.
    */
    'table' => env('SETTINGS_TABLE', 'settings'),

    /*
    |--------------------------------------------------------------------------
    | Nazwy kolumn
    |--------------------------------------------------------------------------
    | Możesz dostosować nazwy kolumn do własnego schematu.
    */
    'columns' => [
        'key'     => 'key',
        'value'   => 'value',
        'user_id' => 'user_id',   // null = ustawienie globalne
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolumna z typem wartości
    |--------------------------------------------------------------------------
    | Jeśli włączona, pakiet zapisuje oryginalny typ PHP (int, bool, float,
    | array, null) i przywraca go przy odczycie. Wymaga dodatkowej kolumny
    | `type` w tabeli (patrz migracja).
    */
    'cast_types' => true,
    'columns_type' => 'type',    // nazwa kolumny przechowującej typ

    /*
    |--------------------------------------------------------------------------
    | Cache plikowy
    |--------------------------------------------------------------------------
    | Ustawienia są buforowane w pliku PHP (opcache-friendly).
    | Plik jest regenerowany przy każdej zmianie wartości.
    |
    | 'enabled'  – czy cache jest aktywny
    | 'path'     – ścieżka do pliku cache (musi być zapisywalna)
    |
    | Przykład ścieżki: storage_path('framework/settings.php')
    */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'path'    => storage_path('framework/settings.php'),
    ],

];
