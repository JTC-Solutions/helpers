<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Tests\Unit;

use JtcSolutions\Helpers\Helper\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    /**
     * @dataProvider provideCamelCaseStrings
     */
    public function testToKebabCase(string $input, string $expected): void
    {
        self::assertSame($expected, StringUtils::toKebabCase($input));
    }

    /**
     * @return array<string[]>
     */
    public static function provideCamelCaseStrings(): array
    {
        return [
            ['camelCase', 'camel-case'],
            ['PascalCase', 'pascal-case'],
            ['XMLHttpRequest', 'x-m-l-http-request'],
            ['simple', 'simple'],
        ];
    }

    /**
     * @dataProvider provideCamelCaseStringsForSnake
     */
    public function testToSnakeCase(string $input, string $expected): void
    {
        self::assertSame($expected, StringUtils::toSnakeCase($input));
    }

    /**
     * @return array<string[]>
     */
    public static function provideCamelCaseStringsForSnake(): array
    {
        return [
            ['camelCase', 'camel_case'],
            ['PascalCase', 'pascal_case'],
            ['XMLHttpRequest', 'x_m_l_http_request'],
            ['simple', 'simple'],
        ];
    }

    /**
     * @dataProvider provideSanitizationStrings
     */
    public function testSanitizeLowercase(string $input, string $expected): void
    {
        self::assertSame($expected, StringUtils::sanitizeLowercase($input));
    }

    /**
     * @return array<string[]>
     */
    public static function provideSanitizationStrings(): array
    {
        return [
            ['  HeLLo World  ', 'hello world'],
            ['TEST', 'test'],
            ["  mixed CASE\n", 'mixed case'],
        ];
    }

    /**
     * @dataProvider provideLowercaseStrings
     */
    public function testToLowercase(string $input, string $expected): void
    {
        self::assertSame($expected, StringUtils::toLowercase($input));
    }

    /**
     * @return array<string[]>
     */
    public static function provideLowercaseStrings(): array
    {
        return [
            ['Hello', 'hello'],
            ['TEST', 'test'],
            ['Already lowercase', 'already lowercase'],
        ];
    }

    /**
     * @dataProvider provideFirstToLowercase
     */
    public function testFirstToLowercase(string $input, string $expected): void
    {
        self::assertSame($expected, StringUtils::firstToLowercase($input));
    }

    /**
     * @return array<string[]>
     */
    public static function provideFirstToLowercase(): array
    {
        return [
            ['HelloWorld', 'helloWorld'],
            ['Test', 'test'],
            ['aAlreadyLower', 'aAlreadyLower'],
            ['', ''],
        ];
    }

    public function testGenerateUrlFriendlyString(): void
    {
        $length = 16;
        $result = StringUtils::generateUrlFriendlyString($length);

        self::assertSame($length, strlen($result));
        self::assertMatchesRegularExpression('/^[a-z0-9]+$/', $result);
    }
}
