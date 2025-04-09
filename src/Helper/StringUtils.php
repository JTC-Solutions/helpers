<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Helper;

final class StringUtils
{
    public static function toKebabCase(string $value): string
    {
        /** @var string $replaced */
        $replaced = preg_replace('/(?<!^)[A-Z]/', '-$0', $value);

        return strtolower($replaced);
    }

    public static function toSnakeCase(string $value): string
    {
        /** @var string $replaced */
        $replaced = preg_replace('/(?<!^)[A-Z]/', '_$0', $value);

        return strtolower($replaced);
    }

    public static function sanitizeLowercase(string $value): string
    {
        return trim(strtolower($value));
    }

    public static function toLowercase(string $value): string
    {
        return strtolower($value);
    }

    public static function firstToLowercase(string $value): string
    {
        return lcfirst($value);
    }

    public static function generateUrlFriendlyString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
