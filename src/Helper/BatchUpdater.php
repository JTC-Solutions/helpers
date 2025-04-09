<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Helper;

use Closure;
use Ramsey\Uuid\Uuid;

/**
 * @template TEntity of object
 * @template TInput of object
 */
final class BatchUpdater
{
    /**
     * @var array<string, TEntity>
     */
    private readonly array $entities;

    /**
     * @var array<string, TInput>
     */
    private readonly array $inputs;

    /**
     * @param TEntity[]               $entities
     * @param Closure(TEntity):string $entityIdGetter
     * @param TInput[]                $inputs
     * @param Closure(TInput): ?string  $inputIdGetter
     */
    public function __construct(
        array $entities,
        Closure $entityIdGetter,
        array $inputs,
        Closure $inputIdGetter,
    ) {
        $entitiesMap = [];
        foreach ($entities as $item) {
            $entitiesMap[$entityIdGetter($item)] = $item;
        }

        $inputsMap = [];
        foreach ($inputs as $input) {
            $inputId = $inputIdGetter($input);
            if ($inputId === null) {
                $inputId = Uuid::uuid4()->toString(); // temporary generate uuid so we get unique and mark it as "for create"
            }
            $inputsMap[$inputId] = $input;
        }

        $this->entities = $entitiesMap;
        $this->inputs = $inputsMap;
    }

    /**
     * @return string[]
     */
    public function getIdsToRemove(): array
    {
        return array_values(
            array_diff(
                array_keys($this->entities),
                array_keys($this->inputs),
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getIdsToBeUpdated(): array
    {
        return array_values(
            array_intersect(
                array_keys($this->entities),
                array_keys($this->inputs),
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getIdsToBeCreated(): array
    {
        return array_values(
            array_diff(
                array_keys($this->inputs),
                array_keys($this->entities),
            ),
        );
    }

    /**
     * @return TInput
     */
    public function getInput(string $id)
    {
        return $this->inputs[$id];
    }

    /**
     * @return TEntity
     */
    public function getEntity(string $id)
    {
        return $this->entities[$id];
    }
}
