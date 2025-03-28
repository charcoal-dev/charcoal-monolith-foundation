<?php
declare(strict_types=1);

namespace App\Shared\Utility;

/**
 * Class ArrayHelper
 * @package App\Shared\Utility
 */
class ArrayHelper
{
    /**
     * @param array $data
     * @param array $excludedKeys
     * @return array
     */
    public static function excludeKeys(array $data, array $excludedKeys = []): array
    {
        if (!$excludedKeys) {
            return $data;
        }

        $excludedKeysLc = array_map("strtolower", $excludedKeys);
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $excludedKeysLc, true)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function canonicalizeLexicographic(array $data): array
    {
        if (!static::isSequential($data)) {
            uksort($data, function ($a, $b) {
                if (ctype_digit((string)$a) && ctype_digit((string)$b)) {
                    return strnatcmp((string)$a, (string)$b);
                }

                return strcmp((string)$a, (string)$b);
            });
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::canonicalizeLexicographic($value);
            }
        }

        return static::isSequential($data) ? $data : (count($data) === 0 ? [] : $data);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function canonicalizeLexicographicJson(array $data): string
    {
        return json_encode(static::canonicalizeLexicographic($data),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function isSequential(array $data): bool
    {
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * @param array|object $input
     * @return array
     * @throws \JsonException
     */
    public static function jsonFilter(array|object $input): array
    {
        return json_decode(json_encode($input), true, flags: JSON_THROW_ON_ERROR);
    }
}