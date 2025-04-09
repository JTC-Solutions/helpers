<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Tests\Unit;

use InvalidArgumentException;
use JtcSolutions\Helpers\Helper\FQCNHelper;
use PHPUnit\Framework\TestCase;

class FQCNHelperTest extends TestCase
{
    /**
     * @dataProvider provideValidFQCNs
     */
    public function testExtractDomainAndEntity(string $fqcn, ?string $expectedDomain, ?string $expectedEntity): void
    {
        $result = FQCNHelper::extractDomainAndEntity($fqcn);

        self::assertSame($expectedDomain, $result['domain']);
        self::assertSame($expectedEntity, $result['entity']);
    }

    /**
     * @return array<string[]>
     */
    public static function provideValidFQCNs(): array
    {
        return [
            ['App\\Domain\\User\\UserEntity', 'Domain', 'UserEntity'],
            ['App\\Entity\\Building', 'Entity', 'Building'],
            ['My\\Custom\\Namespace\\Stuff', 'Custom', 'Stuff'],
            ['A\\B', 'B', 'B'], // edge case: 2 parts only
        ];
    }

    public function testExtractDomainAndEntityThrowsOnInvalidFQCN(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FQCNHelper::extractDomainAndEntity('Invalid');
    }

    /**
     * @dataProvider provideFQCNTransformCases
     */
    public function testTransformFQCNToEntityName(string $fqcn, bool $toLower, string $expected): void
    {
        $result = FQCNHelper::transformFQCNToEntityName($fqcn, $toLower);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<array{string, bool, string}>
     */
    public static function provideFQCNTransformCases(): array
    {
        return [
            ['App\\Entity\\User', true, 'user'],
            ['App\\Entity\\User', false, 'User'],
            ['My\\Fancy\\ClassName', true, 'classname'],
            ['Root\\Something', true, 'something'],
            ['OnePart', true, 'onepart'],
        ];
    }
}
