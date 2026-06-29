<?php

namespace stobys\LaravelSettings\Cache;

/**
 * Serializacja / deserializacja wartości PHP do/z stringa (kolumna `value` w bazie)
 * oraz mapowanie typów.
 */
class ValueCaster
{
    /** Obsługiwane typy */
    private const TYPES = ['string', 'int', 'float', 'bool', 'array', 'null'];

    public static function serialize(mixed $value): array
    {
        $type = match (true) {
            is_null($value)   => 'null',
            is_bool($value)   => 'bool',
            is_int($value)    => 'int',
            is_float($value)  => 'float',
            is_array($value)  => 'array',
            default           => 'string',
        };

        $raw = match ($type) {
            'null'  => '',
            'bool'  => $value ? '1' : '0',
            'array' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            default => (string) $value,
        };

        return ['value' => $raw, 'type' => $type];
    }

    public static function deserialize(string|null $raw, string $type): mixed
    {
        if (!in_array($type, self::TYPES, true)) {
            $type = 'string';
        }

        return match ($type) {
            'null'   => null,
            'bool'   => $raw === '1',
            'int'    => (int) $raw,
            'float'  => (float) $raw,
            'array'  => json_decode($raw ?? '[]', true, 512, JSON_THROW_ON_ERROR),
            default  => (string) ($raw ?? ''),
        };
    }
}
