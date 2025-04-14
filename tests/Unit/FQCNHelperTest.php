<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Tests\Unit;

use InvalidArgumentException;
use JtcSolutions\Helpers\Helper\FQCNHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FQCNHelperTest extends TestCase
{
    #[DataProvider('provideValidFqcnForDomainEntity')]
    public function testExtractDomainAndEntitySuccess(string $fqcn, array $expected): void
    {
        self::assertSame($expected, FQCNHelper::extractDomainAndEntity($fqcn));
    }

    /**
     * @return array<string, array{0: string, 1: array{domain: string, entity: string}}>
     */
    public static function provideValidFqcnForDomainEntity(): array
    {
        return [
            'Standard FQCN' => ["App\DomainDirectory\Domain\Entity\EntityClass", ['domain' => 'DomainDirectory', 'entity' => 'EntityClass']],
            'Leading Slash' => ["\App\Customer\Domain\Model\User", ['domain' => 'Customer', 'entity' => 'User']],
            'Short FQCN (2 parts)' => ["App\MyService", ['domain' => 'MyService', 'entity' => 'MyService']],
            'Vendor Namespace' => ["Vendor\Package\Sub\MyHelper", ['domain' => 'Package', 'entity' => 'MyHelper']],
            'Deeply Nested' => ["A\B\C\D\E\F\G", ['domain' => 'B', 'entity' => 'G']],
        ];
    }

    #[DataProvider('provideInvalidFqcnForDomainEntity')]
    public function testExtractDomainAndEntityThrowsException(string $invalidFqcn): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is not a valid FQCN with at least two segments/');
        FQCNHelper::extractDomainAndEntity($invalidFqcn);
    }

    /**
     * @return array<string , string[]>
     */
    public static function provideInvalidFqcnForDomainEntity(): array
    {
        return [
            'Single Class Name' => ['MyClass'],
            'Empty String' => [''],
            'Single Namespace Root' => ['App'], // Only one part after explode
            'Leading Slash Only' => ['\\'], // Represents a single backslash
        ];
    }

    // --- Tests for transformFQCNToShortClassName ---

    #[DataProvider('provideFqcnForShortName')]
    public function testTransformFQCNToShortClassName(string $fqcn, bool $toLowercase, string $expected): void
    {
        // Assuming the method was renamed as suggested; if not, change the name here.
        self::assertSame($expected, FQCNHelper::transformFQCNToShortClassName($fqcn, $toLowercase));
    }

    /**
     * @return array<string, array{0: string, 1: bool, 2: string}>
     */
    public static function provideFqcnForShortName(): array
    {
        return [
            'Standard FQCN, uppercase' => ["App\Domain\Entity\EntityClass", false, 'EntityClass'],
            'Standard FQCN, lowercase' => ["App\Domain\Entity\EntityClass", true, 'entityclass'],
            'Leading Slash, uppercase' => ["\App\Service\MyService", false, 'MyService'],
            'Leading Slash, lowercase' => ["\App\Service\MyService", true, 'myservice'],
            'Global Class, uppercase' => ['GlobalClass', false, 'GlobalClass'],
            'Global Class, lowercase' => ['GlobalClass', true, 'globalclass'],
            'Empty String' => ['', false, ''],
            'String with single backslash' => ['\\', false, ''], // basename(str_replace('\\','/','\')) -> basename('/') -> ''
            'FQCN with numbers' => ["App\V1\Model\Data2", false, 'Data2'],
            'FQCN with numbers, lowercase' => ["App\V1\Model\Data2", true, 'data2'],
        ];
    }

    // --- Tests for convertNamespaceToFilepath ---

    #[DataProvider('provideNamespaceToPathData')]
    public function testConvertNamespaceToFilepath(string $namespace, string $baseNamespace, string $baseDir, string $expected): void
    {
        // Replace / with DIRECTORY_SEPARATOR in expected path for cross-platform compatibility
        $expectedPath = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        self::assertSame($expectedPath, FQCNHelper::convertNamespaceToFilepath($namespace, $baseNamespace, $baseDir));
    }

    /**
     * @return array<string, string[]>
     */
    public static function provideNamespaceToPathData(): array
    {
        // Using forward slashes '/' in expected paths for readability. Test converts them.
        return [
            'Standard PSR-4' => ["App\Domain\Entity\User", 'App', 'src', 'src/Domain/Entity/User'],
            'Vendor PSR-4' => ["Vendor\Library\Component\Helper", "Vendor\Library", 'lib', 'lib/Component/Helper'],
            'Leading slash namespace' => ["\App\Service\Runner", 'App', 'app', 'app/Service/Runner'],
            'Base NS with trailing slash' => ["App\Sub\Data", 'App\\', 'src', 'src/Sub/Data'], // "App\\" -> literal "App\"
            'Base Dir with trailing slash' => ["App\Sub\Data", 'App', 'src/', 'src/Sub/Data'],
            'Both with trailing slash' => ["App\Sub\Data", 'App\\', 'src/', 'src/Sub/Data'],
            'Base Dir with deep path' => ["App\Web\Controller", 'App', '/var/www/project/src', '/var/www/project/src/Web/Controller'],
            'Namespace is base namespace' => ['App', 'App', 'src', 'src'],
            'Namespace is base ns (trail)' => ['App\\', 'App\\', 'src/', 'src'], // "App\\" -> literal "App\"
            'NS not starting with base' => ["Other\Lib\Util", 'App', 'src', 'src/Other/Lib/Util'],
            'Empty base namespace' => ["MyLib\MyClass", '', 'libs', 'libs/MyLib/MyClass'],
            'Empty base dir' => ["MyLib\MyClass", 'MyLib', '', 'MyClass'],
            'Base dir is root' => ["MyLib\MyClass", 'MyLib', '/', '/MyClass'],
        ];
    }

    // --- Tests for convertPathToNamespace ---

    #[DataProvider('providePathToNamespaceData')]
    public function testConvertPathToNamespace(string $path, string $baseNamespace, string $baseDir, string $expected): void
    {
        // Normalize separators in input path to DIRECTORY_SEPARATOR for testing consistency
        $normalizedPathInput = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        // Expected namespace should always use '\'
        $expectedNamespace = str_replace('/', '\\', $expected); // Ensure expected uses '\'

        self::assertSame($expectedNamespace, FQCNHelper::convertPathToNamespace($normalizedPathInput, $baseNamespace, $baseDir));
    }

    /**
     * @return array<string, string[]>
     */
    public static function providePathToNamespaceData(): array
    {
        // Using forward slashes '/' in input paths for readability. Test normalizes them.
        // Expected namespaces use backslashes '\'.
        return [
            'Standard PSR-4' => ['src/Domain/Entity/User', 'App', 'src', "App\Domain\Entity\User"],
            'Vendor PSR-4' => ['lib/Component/Helper', "Vendor\Library", 'lib', "Vendor\Library\Component\Helper"],
            'Base NS with trailing slash' => ['src/Sub/Data', 'App\\', 'src', "App\Sub\Data"], // "App\\" -> literal "App\"
            'Base Dir with trailing slash' => ['src/Sub/Data', 'App', 'src/', "App\Sub\Data"],
            'Both with trailing slash' => ['src/Sub/Data', 'App\\', 'src/', "App\Sub\Data"],
            'Base Dir with deep path' => ['/var/www/project/src/Web/Controller', 'App', '/var/www/project/src', "App\Web\Controller"],
            'Path is base dir' => ['src', 'App', 'src', 'App'],
            'Path is base dir (trail)' => ['src/', 'App', 'src/', 'App'],
            'Path does not start with base' => ['unrelated/path/File', 'App', 'src', 'App\\unrelated\\path\\File'], // Note the double backslash needed here in the expected string literal
            'Path with .php extension' => ['src/MyClass.php', 'App', 'src', "App\MyClass.php"], // Extension is kept
            'Empty base namespace' => ['libs/MyLib/MyClass', '', 'libs', "MyLib\MyClass"],
            'Empty base dir' => ['MyClass', 'MyLib', '', "MyLib\MyClass"],
            'Base dir is root' => ['/MyClass', 'MyLib', '/', "MyLib\MyClass"],
            'Windows style path input' => ["src\Domain\Service", 'App', 'src', "App\Domain\Service"], // Will be normalized by test
        ];
    }


    // --- Tests for extractNamespaceFromFQCN ---

    #[DataProvider('provideFqcnForNamespaceExtraction')]
    public function testExtractNamespaceFromFQCN(string $fqcn, string $expected): void
    {
        self::assertSame($expected, FQCNHelper::extractNamespaceFromFQCN($fqcn));
    }

    /**
     * @return array<string, string[]>
     */
    public static function provideFqcnForNamespaceExtraction(): array
    {
        return [
            'Standard FQCN' => ["App\Domain\Entity\EntityClass", "App\Domain\Entity"],
            'Leading Slash FQCN' => ["\App\Service\MyService", "App\Service"],
            'Two part FQCN' => ["Vendor\Package", 'Vendor'],
            'Global Class' => ['MyGlobalClass', ''],
            'Empty String' => ['', ''],
            'Only Namespace Separator' => ['\\', ''], // ltrim makes it "", returns ""
            'Namespace only trail slash' => ["App\Domain\\", "App\Domain"], // "App\Domain\\" -> literal "App\Domain\"
            'FQCN with numbers' => ["App\V1\Model\Data2", "App\V1\Model"],
        ];
    }
}
