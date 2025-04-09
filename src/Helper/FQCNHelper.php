<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Helper;

use InvalidArgumentException;

final class FQCNHelper
{
    /**
     * @return array{"domain": string|null, "entity": string|null}
     */
    public static function extractDomainAndEntity(string $fqcn): array
    {
        $parts = explode('\\', $fqcn);

        if (count($parts) < 2) {
            throw new InvalidArgumentException('Invalid class name format');
        }

        $domain = $parts[1] ?? null; // The second part
        $entity = $parts[count($parts) - 1] ?? null; // The last part

        return [
            'domain' => $domain,
            'entity' => $entity,
        ];
    }

    /**
     * Transforms input like App\Entity\Building into "building"
     */
    public static function transformFQCNToEntityName(string $fqcn, bool $toLowercase = true): string
    {
        $className = basename(str_replace('\\', '/', $fqcn));

        if ($toLowercase === true) {
            $className = strtolower($className);
        }

        return $className;
    }
}
