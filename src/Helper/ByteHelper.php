<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Helper;

class ByteHelper
{
    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        /** @psalm-suppress PossiblyInvalidArrayOffset */
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
