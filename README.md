# JtcSolutions PHP Helpers Library

A collection of helpful utility classes for common PHP tasks.

## Installation

(You'll need to add installation instructions here, typically using Composer)

```bash
composer require jtc-solutions/helpers
```

## Usage
This library provides several static helper classes under the JtcSolutions\Helpers\Helper namespace.

### ArrayUtils

```php
use JtcSolutions\Helpers\Helper\ArrayUtils;

// Check if an array contains duplicate values
$hasDuplicates = ArrayUtils::containsDuplicates([1, 2, 3, 2, 5]); // true
$noDuplicates = ArrayUtils::containsDuplicates(['a', 'b', 'c']); // false
```

### BatchUpdater
A helper class to determine which items need to be created, updated, or removed when comparing a list of existing entities with a list of input data transfer objects (DTOs) or similar structures.
Useful for synchronizing data, especially collections within an aggregate root in DDD.

```php
use JtcSolutions\Helpers\Helper\BatchUpdater;

// Assume $existingEntities is an array of your entity objects (e.g., from DB)
// Assume $inputDtos is an array of input data objects (e.g., from request)

// Example Entity & Input DTO (replace with your actual classes)
class MyEntity { public function __construct(public string $id, public string $data) {} }
class MyInput { public function __construct(public ?string $id, public string $data) {} }

$existingEntities = [
    new MyEntity('id1', 'old data 1'),
    new MyEntity('id2', 'old data 2'), // This one will be removed
    new MyEntity('id3', 'old data 3'), // This one will be updated
];

$inputDtos = [
    new MyInput('id1', 'old data 1'), // Unchanged (will be in update list)
    new MyInput('id3', 'new data 3'), // Needs update
    new MyInput(null, 'new data 4'),  // Needs creation
];

$entityIdGetter = fn(MyEntity $entity): string => $entity->id;
$inputIdGetter = fn(MyInput $input): ?string => $input->id;

$updater = new BatchUpdater($existingEntities, $entityIdGetter, $inputDtos, $inputIdGetter);

$idsToRemove = $updater->getIdsToRemove(); // ['id2']
$idsToUpdate = $updater->getIdsToBeUpdated(); // ['id1', 'id3']
$idsToCreate = $updater->getIdsToBeCreated(); // [generated_uuid_for_new_data_4]

// Access specific input/entity data using the ID
$inputForUpdate = $updater->getInput('id3'); // MyInput object with 'new data 3'
$entityForUpdate = $updater->getEntity('id3'); // MyEntity object with 'old data 3'
$inputForCreate = $updater->getInput($idsToCreate[0]); // MyInput object with 'new data 4'

// You can now loop through these IDs to perform persistence operations.
```

### ByteHelper

```php
use JtcSolutions\Helpers\Helper\ByteHelper;

echo ByteHelper::formatBytes(1024);    // 1 KB
echo ByteHelper::formatBytes(1500000); // 1.43 MB
echo ByteHelper::formatBytes(0);       // 0 B
```

### FQCNHelper
Utilities for working with Fully Qualified Class Names (FQCNs), namespaces, and file paths. Assumes PSR-4 structure for conversions.

```php
use JtcSolutions\Helpers\Helper\FQCNHelper;

$fqcn = "App\Domain\Entity\User";

// Extract 'domain' (2nd segment) and 'entity' (last segment)
$parts = FQCNHelper::extractDomainAndEntity($fqcn);
// $parts = ['domain' => 'Domain', 'entity' => 'User']

// Get short class name
$shortName = FQCNHelper::transformFQCNToShortClassName($fqcn); // "User"
$lowerShortName = FQCNHelper::transformFQCNToShortClassName($fqcn, true); // "user"

// Convert namespace to path
$path = FQCNHelper::convertNamespaceToFilepath($fqcn, "App", "src");
// $path = "src/Domain/Entity/User" (using / as separator)

// Convert path to namespace
$namespace = FQCNHelper::convertPathToNamespace("src/Service/AuthService", "App", "src");
// $namespace = "App\Service\AuthService"

// Extract namespace part from FQCN
$nsOnly = FQCNHelper::extractNamespaceFromFQCN($fqcn);
// $nsOnly = "App\Domain\Entity"
```

### StringUtils
```php
use JtcSolutions\Helpers\Helper\StringUtils;

// Case conversions
echo StringUtils::toKebabCase("helloWorldExample"); // hello-world-example
echo StringUtils::toSnakeCase("myVariableName");  // my_variable_name

// Lowercase / Sanitization
echo StringUtils::sanitizeLowercase("  Some String  "); // some string
echo StringUtils::toLowercase("UPPERCASE");          // uppercase
echo StringUtils::firstToLowercase("HelloWorld");    // helloWorld

// Random string generation
$random = StringUtils::generateUrlFriendlyString(10); // e.g., "a3b7x9p2q1"
```