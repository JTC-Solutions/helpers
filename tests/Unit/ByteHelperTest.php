<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Tests\Unit;

use JtcSolutions\Helpers\Helper\ByteHelper;
use PHPUnit\Framework\TestCase;

class ByteHelperTest extends TestCase
{
    /**
     * @dataProvider provideByteValues
     */
    public function testFormatBytes(int $bytes, string $expected): void
    {
        self::assertSame($expected, ByteHelper::formatBytes($bytes));
    }

    /**
     * @return array<int, array{int, string}>
     */
    public static function provideByteValues(): array
    {
        return [
            [0, '0 B'],
            [1, '1 B'],
            [512, '512 B'],
            [1_024, '1 KB'],
            [1_536, '1.5 KB'],
            [1_048_576, '1 MB'], // 1024 * 1024
            [1_572_864, '1.5 MB'],
            [1_073_741_824, '1 GB'], // 1024^3
            [1_610_612_736, '1.5 GB'],
            [1_099_511_627_776, '1 TB'], // 1024^4
            [1_649_267_441_664, '1.5 TB'],
        ];
    }
}
