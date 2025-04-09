<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Tests\Unit;

use JtcSolutions\Helpers\Helper\ArrayUtils;
use PHPUnit\Framework\TestCase;

class ArrayUtilsTest extends TestCase
{
    public function testReturnsFalseForEmptyArray(): void
    {
        self::assertFalse(ArrayUtils::containsDuplicates([]));
    }

    public function testReturnsFalseForUniqueStrings(): void
    {
        self::assertFalse(ArrayUtils::containsDuplicates(['apple', 'banana', 'cherry']));
    }

    public function testReturnsTrueForDuplicateStrings(): void
    {
        self::assertTrue(ArrayUtils::containsDuplicates(['apple', 'banana', 'apple']));
    }

    public function testReturnsFalseForUniqueIntegers(): void
    {
        self::assertFalse(ArrayUtils::containsDuplicates([1, 2, 3, 4]));
    }

    public function testReturnsTrueForDuplicateIntegers(): void
    {
        self::assertTrue(ArrayUtils::containsDuplicates([1, 2, 2, 3]));
    }

    public function testReturnsFalseForUniqueFloats(): void
    {
        self::assertFalse(ArrayUtils::containsDuplicates([1.1, 2.2, 3.3]));
    }

    public function testReturnsTrueForDuplicateFloats(): void
    {
        self::assertTrue(ArrayUtils::containsDuplicates([1.1, 2.2, 1.1]));
    }

    public function testMixedTypesAreStillComparedCorrectly(): void
    {
        // PHP considers 1 and "1" as equal in loose comparisons, but array_unique uses strict comparison by default with SORT_REGULAR
        self::assertTrue(ArrayUtils::containsDuplicates(['1', 1]));
    }
}
