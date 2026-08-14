<?php

namespace App\Collectors\Support;

use JsonException;

final class CanonicalJson
{
    /**
     * @param  array<string|int, mixed>  $value
     *
     * @throws JsonException
     */
    public static function encode(array $value): string
    {
        self::sortRecursively($value);

        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
        );
    }

    /** @param array<string|int, mixed> $value */
    private static function sortRecursively(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                self::sortRecursively($item);
            }
        }

        unset($item);

        if (! array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
    }
}
