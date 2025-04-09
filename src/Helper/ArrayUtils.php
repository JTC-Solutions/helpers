<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Helper;

class ArrayUtils
{
    /**
     * @param string[]|int[]|float[] $array
     */
    public static function containsDuplicates(array $array): bool
    {
        return count($array) !== count(array_unique($array, SORT_REGULAR));
    }
}
