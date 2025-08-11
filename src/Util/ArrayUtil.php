<?php

namespace App\Util;

/**
 * Utility class to manipulate arrays.
 */
class ArrayUtil
{
    /**
     * Merge two arrays recursively.
     *
     * If a key exists in both arrays and both values are:
     * - arrays: these two values are merged recursively, except for keys in `$doNotMergeRecursively`.
     * - not arrays: the second value overwrites the first one.
     *
     * @param array $arr1 The first array to merge.
     * @param array $arr2 The second array to merge (overwrites the first one).
     * @param array $doNotMergeRecursively Keys that should not be merged recursively.
     *
     * @return array The merged array.
     */
    public static function mergeRecursiceDistinct(array &$arr1, array &$arr2, array $doNotMergeRecursively = []): array
    {
        $merged = $arr1;

        foreach ($arr2 as $key => &$value) {
            if (is_array($value) && is_array($merged[$key] ?? false) && !in_array($key, $doNotMergeRecursively, true)) {
                $merged[$key] = static::mergeRecursiceDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
