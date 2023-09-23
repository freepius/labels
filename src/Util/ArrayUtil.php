<?php

namespace App\Util;

/**
 * Utility class to manipulate arrays.
 */
class ArrayUtil
{
    /**
     * Merge two arrays recursively. If a key exists in both arrays and both values are:
     * - arrays: these two values are merged recursively
     * - not arrays: the value from the second array overwrites the one from the first array.
     *
     * @param array $arr1 The first array to merge.
     * @param array $arr2 The second array to merge (overwrites the first one).
     *
     * @return array The merged array.
     */
    public static function mergeRecursiceDistinct(array &$arr1, array &$arr2): array
    {
        $merged = $arr1;

        foreach ($arr2 as $key => &$value) {
            if (is_array($value) && is_array($merged[$key] ?? false)) {
                $merged[$key] = static::mergeRecursiceDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
