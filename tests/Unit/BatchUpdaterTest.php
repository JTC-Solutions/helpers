<?php declare(strict_types = 1);

namespace JtcSolutions\Helpers\Tests\Unit;

use JtcSolutions\Helpers\Helper\BatchUpdater;
use PHPUnit\Framework\TestCase;

class BatchUpdaterTest extends TestCase
{
    public function testItDetectsIdsToBeCreated(): void
    {
        $entities = [
            $this->makeEntity('1'),
            $this->makeEntity('2'),
        ];

        $inputs = [
            $this->makeInput('2'), // update
            $this->makeInput(null), // new (create)
        ];

        $updater = new BatchUpdater(
            $entities,
            static fn ($e) => $e->id,
            $inputs,
            static fn ($i) => $i->id,
        );

        $createdIds = $updater->getIdsToBeCreated();

        self::assertCount(1, $createdIds);
        self::assertIsString($createdIds[0]);
        self::assertNotContains('1', $createdIds);
        self::assertNotContains('2', $createdIds);
    }

    public function testItDetectsIdsToRemove(): void
    {
        $entities = [
            $this->makeEntity('1'),
            $this->makeEntity('2'),
            $this->makeEntity('3'),
        ];

        $inputs = [
            $this->makeInput('1'),
            $this->makeInput('3'),
        ];

        $updater = new BatchUpdater(
            $entities,
            static fn ($e) => $e->id,
            $inputs,
            static fn ($i) => $i->id,
        );

        self::assertEquals(['2'], $updater->getIdsToRemove());
    }

    public function testItDetectsIdsToUpdate(): void
    {
        $entities = [
            $this->makeEntity('a'),
            $this->makeEntity('b'),
        ];

        $inputs = [
            $this->makeInput('a'),
            $this->makeInput('x'),
        ];

        $updater = new BatchUpdater(
            $entities,
            static fn ($e) => $e->id,
            $inputs,
            static fn ($i) => $i->id,
        );

        self::assertEquals(['a'], $updater->getIdsToBeUpdated());
    }

    public function testItReturnsEntitiesAndInputsById(): void
    {
        $entity = $this->makeEntity('ent');
        $input = $this->makeInput('inp');

        $updater = new BatchUpdater(
            [$entity],
            static fn ($e) => $e->id,
            [$input],
            static fn ($i) => $i->id,
        );

        self::assertSame($entity, $updater->getEntity('ent'));
        self::assertSame($input, $updater->getInput('inp'));
    }

    private function makeEntity(string $id): object
    {
        return (object) ['id' => $id];
    }

    private function makeInput(?string $id): object
    {
        return (object) ['id' => $id];
    }
}
