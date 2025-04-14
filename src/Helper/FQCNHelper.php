<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Helper;

use InvalidArgumentException;

/**
 * Provides utility functions for manipulating Fully Qualified Class Names (FQCNs),
 * namespaces, and corresponding file paths.
 *
 * This helper facilitates common tasks like extracting parts of an FQCN,
 * converting between namespaces and file paths (assuming a PSR-4 like structure),
 * and retrieving specific components like the short class name or the namespace.
 *
 * As a final class with only static methods, it acts as a stateless utility collection.
 */
final class FQCNHelper
{
    /**
     * Extracts the presumed 'domain' (second segment) and 'entity' (last segment)
     *  from a Fully Qualified Class Name (FQCN).
     *
     * Note: The definition of 'domain' as strictly the second segment might be
     *  specific to a particular project structure (e.g., App\DomainDirectory\...).
     *  If the FQCN has only two parts (e.g., App\MyClass), both 'domain' and
     *  'entity' will be assigned the second part ('MyClass').
     *
     * @param string $fullyQualifiedClassName The FQCN to parse (e.g., "App\DomainDirectory\Domain\Entity\EntityClass").
     *  Must contain at least one namespace separator ('\').
     *
     * @return array{"domain": string|null, "entity": string|null} An associative array containing:
     *  - 'domain': The second segment of the FQCN (e.g., "DomainDirectory"). Null if extraction fails (though usually throws).
     *  - 'entity': The last segment (class name) of the FQCN (e.g., "EntityClass"). Null if extraction fails (though usually throws).
     *
     * @throws InvalidArgumentException If the input string does not contain at least two segments separated by '\'.
     *
     * @example
     *  ```php
     *  FQCNHelper::extractDomainAndEntity("App\Customer\Domain\Entity\Address");
     *  // Returns ['domain' => 'Customer', 'entity' => 'Address']
     *
     * FQCNHelper::extractDomainAndEntity("App\User");
     *  // Returns ['domain' => 'User', 'entity' => 'User']
     *
     * FQCNHelper::extractDomainAndEntity("MyClass"); // Throws InvalidArgumentException
     * ```
     */
    public static function extractDomainAndEntity(string $fullyQualifiedClassName): array
    {
        // Trim leading backslash if present for consistency
        $normalizedFqcn = ltrim($fullyQualifiedClassName, '\\');
        $parts = explode('\\', $normalizedFqcn);

        // Need at least 'App' and 'Something' -> 2 parts minimum.
        if (count($parts) < 2) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid FQCN with at least two segments for %s::%s. Expected format like "Vendor\Component\Class".',
                    $fullyQualifiedClassName,
                    self::class,
                    __FUNCTION__,
                ),
            );
        }

        // The second part (index 1) is considered the 'domain'
        $domain = $parts[1] ?? null;
        // The last part is the 'entity' or class name
        $entity = $parts[count($parts) - 1] ?? null;

        return [
            'domain' => $domain,
            'entity' => $entity,
        ];
    }

    /**
     * Extracts the short class name (the part after the last namespace separator)
     * from a Fully Qualified Class Name (FQCN).
     *
     * @param string $fullyQualifiedClassName The FQCN (e.g., "App\Domain\Entity\EntityClass").
     * Can handle leading backslashes.
     * @param bool $toLowercase If set to true, the extracted class name is converted to lowercase.
     *
     * @return string The short class name (e.g., "EntityClass" or "entityclass" if $toLowercase is true).
     * Returns the input string itself if no namespace separator is found.
     *
     * @example
     * ```php
     * FQCNHelper::transformFQCNToShortClassName("App\Domain\Entity\EntityClass");
     * // Returns "EntityClass"
     *
     * FQCNHelper::transformFQCNToShortClassName("App\Domain\Entity\EntityClass", true);
     * // Returns "entityclass"
     *
     * FQCNHelper::transformFQCNToShortClassName("\App\MyService");
     * // Returns "MyService"
     *
     * FQCNHelper::transformFQCNToShortClassName("MyClass");
     * // Returns "MyClass"
     * ```
     */
    public static function transformFQCNToShortClassName(string $fullyQualifiedClassName, bool $toLowercase = false): string
    {
        // Using basename after replacing separators is robust for getting the last segment.
        // str_replace works even if the input doesn't contain '\'.
        // basename handles potential leading/trailing slashes introduced by replacement.
        $className = basename(str_replace('\\', '/', $fullyQualifiedClassName));

        if ($toLowercase === true) {
            $className = strtolower($className);
        }

        return $className;
    }

    /**
     * Converts a namespace into a corresponding file system path, assuming a PSR-4 mapping.
     *
     * It removes the provided `$baseNamespace` prefix from the `$namespace` and prepends
     * the `$baseDir`, replacing namespace separators ('\') with directory separators.
     *
     * @param string $namespace The namespace to convert (e.g., "App\Domain\Service\MyService").
     * @param string $baseNamespace The base namespace prefix to remove (e.g., "App").
     * @param string $baseDir The base directory corresponding to the base namespace (e.g., "src", "/", "/var/www").
     *
     * @return string The calculated file system path (e.g., "src/Domain/Service/MyService").
     * Does not typically include the file extension (like ".php").
     *
     * @example
     * ```php
     * FQCNHelper::convertNamespaceToFilepath("App\Domain\Entity\User", "App", "src");
     * // Returns "src/Domain/Entity/User"
     * FQCNHelper::convertNamespaceToFilepath("App", "App", "src");
     * // Returns "src"
     * FQCNHelper::convertNamespaceToFilepath("MyLib\MyClass", "MyLib", "");
     * // Returns "MyClass"
     * FQCNHelper::convertNamespaceToFilepath("MyLib\MyClass", "MyLib", "/");
     * // Returns "/MyClass"
     * FQCNHelper::convertNamespaceToFilepath("MyLib", "MyLib", "/");
     * // Returns "/"
     * ```
     */
    public static function convertNamespaceToFilepath(
        string $namespace,
        string $baseNamespace,
        string $baseDir,
    ): string {
        // Normalize namespaces by trimming surrounding '\'
        $normalizedNamespace = trim($namespace, '\\');
        $cleanBaseNamespace = trim($baseNamespace, '\\');

        // --- FIX: Handle baseDir normalization correctly, especially for root '/' ---
        $cleanBaseDir = $baseDir;
        // Only apply rtrim if the baseDir is not exactly the DIRECTORY_SEPARATOR
        if ($baseDir !== DIRECTORY_SEPARATOR) {
            $cleanBaseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);
        }
        // Now $cleanBaseDir is '/' if $baseDir was '/', otherwise it's potentially trimmed.

        // Handle cases where the result should just be the base directory path
        if ($normalizedNamespace === $cleanBaseNamespace || $normalizedNamespace === '') {
            // Return the potentially non-trimmed root '/' or the trimmed path
            return $cleanBaseDir;
        }

        // Determine the prefix to check (needs trailing '\' if base namespace isn't empty)
        $baseNsPrefix = $cleanBaseNamespace === '' ? '' : $cleanBaseNamespace . '\\';

        $relativeNamespace = $normalizedNamespace;
        // Check if the namespace starts with the base namespace prefix and remove it
        if ($baseNsPrefix !== '' && str_starts_with($normalizedNamespace, $baseNsPrefix)) {
            $relativeNamespace = substr($normalizedNamespace, strlen($baseNsPrefix));
        }

        // Convert remaining namespace part to a relative file path
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace);

        // If stripping the prefix resulted in an empty relative path (e.g., input "App\\", base "App")
        if ($relativePath === '') {
            // Return the potentially non-trimmed root '/' or the trimmed path
            return $cleanBaseDir;
        }

        // --- Revised Concatenation Logic ---
        // If the cleaned base directory is the root separator, prepend it directly.
        if ($cleanBaseDir === DIRECTORY_SEPARATOR) {
            // Ensure we don't get "//" if relativePath somehow starts with / (though unlikely)
            return DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
        }
        // If the cleaned base directory ended up empty (meaning original $baseDir was empty)
        if ($cleanBaseDir === '') {
            return $relativePath;
        }

        // Otherwise (non-root, non-empty base path), join with a separator.
        return $cleanBaseDir . DIRECTORY_SEPARATOR . $relativePath;
    }

    /**
     * Converts a file system path back into a namespace, assuming a PSR-4 mapping.
     *
     * It removes the provided `$baseDir` prefix from the `$path` and prepends
     * the `$baseNamespace`, replacing directory separators with namespace separators ('\').
     * Assumes the input path does *not* include a file extension (like ".php").
     *
     * @param string $path The file system path (e.g., "src/Domain/Entity/User"). Can use '/' or '\'.
     * @param string $baseNamespace The base namespace corresponding to the base directory (e.g., "App").
     * @param string $baseDir The base directory prefix to remove from the path (e.g., "src").
     *
     * @return string The calculated namespace (e.g., "App\Domain\Entity\User").
     *
     * @example
     * ```php
     * FQCNHelper::convertPathToNamespace("src/Domain/Entity/User", "App", "src");
     * // Returns "App\Domain\Entity\User"
     *
     * FQCNHelper::convertPathToNamespace("lib/Component/Helper", "Vendor\Library", "lib");
     * // Returns "Vendor\Library\Component\Helper"
     *
     * FQCNHelper::convertPathToNamespace("/path/to/project/app/Sub/Data", "App\\", "/path/to/project/app/");
     * // Returns "App\Sub\Data" (Handles trailing slashes)
     *
     * FQCNHelper::convertPathToNamespace("src/MyClass.php", "App", "src");
     * // Returns "App\MyClass.php" - Note: Does not strip extension!
     *
     * FQCNHelper::convertPathToNamespace("unrelated/path/File", "App", "src");
     * // Returns "App\unrelated\path\File" (If path doesn't start with baseDir, it's appended directly)
     * ```
     */
    public static function convertPathToNamespace(
        string $path,
        string $baseNamespace,
        string $baseDir,
    ): string {
        // Normalize slashes to current OS directory separator for consistent processing
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        // Normalize base directory path
        $normalizedBaseDir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDir), DIRECTORY_SEPARATOR);

        $relativePath = $normalizedPath;
        // Check if the path starts with the base directory and remove it
        $baseDirPrefix = $normalizedBaseDir . DIRECTORY_SEPARATOR;
        if ($normalizedBaseDir === '') { // Handle baseDir being root '/' or empty
            $baseDirPrefix = DIRECTORY_SEPARATOR;
            // Avoid removing leading separator if path itself starts with it
            if (str_starts_with($normalizedPath, $baseDirPrefix)) {
                $relativePath = substr($normalizedPath, strlen($baseDirPrefix));
            }
        } elseif (str_starts_with($normalizedPath, $baseDirPrefix)) {
            $relativePath = substr($normalizedPath, strlen($baseDirPrefix));
        } elseif ($normalizedPath === $normalizedBaseDir) {
            // If the path *is* the base dir, the relative path is empty
            $relativePath = '';
        }


        // Convert directory separators to namespace backslashes
        $relativeNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        // Clean base namespace and join
        $cleanBaseNamespace = rtrim($baseNamespace, '\\');

        // Handle empty relative namespace (path was the base directory)
        if (empty($relativeNamespace)) {
            return $cleanBaseNamespace;
        }

        // Handle empty base namespace
        if (empty($cleanBaseNamespace)) {
            return $relativeNamespace;
        }

        return $cleanBaseNamespace . '\\' . $relativeNamespace;
    }

    /**
     * Extracts the namespace part from a Fully Qualified Class Name (FQCN).
     *
     * @param string $fullyQualifiedClassName The FQCN (e.g., "App\Domain\Entity\EntityClass").
     * Can handle leading backslashes.
     *
     * @return string The namespace part (e.g., "App\Domain\Entity"), or an empty string
     * if the class is in the global namespace (no '\' found).
     *
     * @example
     * ```php
     * FQCNHelper::extractNamespaceFromFQCN("App\Domain\Entity\EntityClass");
     * // Returns "App\Domain\Entity"
     *
     * FQCNHelper::extractNamespaceFromFQCN("\App\Service\MyService");
     * // Returns "App\Service"
     *
     * FQCNHelper::extractNamespaceFromFQCN("MyGlobalClass");
     * // Returns ""
     *
     * FQCNHelper::extractNamespaceFromFQCN("");
     * // Returns ""
     * ```
     */
    public static function extractNamespaceFromFQCN(string $fullyQualifiedClassName): string
    {
        $fqcn = ltrim($fullyQualifiedClassName, '\\');

        // Find the last backslash
        $lastBackslashPos = strrpos($fqcn, '\\');

        // If no backslash, it's a global class (no namespace) or empty string
        if ($lastBackslashPos === false) {
            return '';
        }

        // Return everything before the last backslash
        return substr($fqcn, 0, $lastBackslashPos);
    }
}
