<?php

declare(strict_types=1);

namespace App\Tests\Unit\Milk;

use App\Migration\M261031181800CreateMilkTables;
use App\Milk\MilkRepository;
use App\Milk\NoSpaceException;
use App\Milk\TankAllocator;
use App\Milk\TankState;
use Codeception\Test\Unit;
use DateTimeImmutable;
use InvalidArgumentException;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertSame;

final class MilkRepositoryTest extends Unit
{
    private Connection $db;
    private MilkRepository $repository;

    protected function _before(): void
    {
        $this->db = new Connection(new Driver(new Dsn(databaseName: 'memory')));
        $this->db->createCommand('PRAGMA foreign_keys = ON')->execute();
        (new M261031181800CreateMilkTables())->up(new MigrationBuilder($this->db, new NullMigrationInformer()));
        $this->repository = new MilkRepository($this->db, new TankAllocator());
    }

    public function testStartsWithFiveEmptyTanksOf300Liters(): void
    {
        $tanks = $this->repository->tanks();

        assertCount(5, $tanks);
        foreach ($tanks as $index => $tank) {
            assertSame($index + 1, $tank->id);
            assertSame(TankState::MAX_CAPACITY, $tank->capacity);
            assertSame(0, $tank->currentLiters);
        }
    }

    public function testPoursIntoTheEmptiestTankAndShowsCurrentVolume(): void
    {
        $at = new DateTimeImmutable('2026-10-31 19:00:00');

        $first = $this->repository->addPour('  Иван  ', 100, $at);
        $second = $this->repository->addPour('Мария', 100, $at->modify('+1 minute'));

        assertSame(1, $first->tankId);
        assertSame(100, $first->tankCurrentLiters);
        assertSame('Иван', $first->personName);
        assertSame(2, $second->tankId);

        $tanks = $this->repository->tanks();
        assertSame(100, $tanks[0]->currentLiters);
        assertSame(100, $tanks[1]->currentLiters);
        assertSame(0, $tanks[2]->currentLiters);

        $pours = $this->repository->pours();
        assertSame('Мария', $pours[0]->personName);
        assertSame(2, $pours[0]->tankId);
        assertSame(100, $pours[0]->tankCurrentLiters);
        assertSame('Иван', $pours[1]->personName);
        assertSame(100, $pours[1]->tankCurrentLiters);
        assertSame('2026-10-31 19:00:00', $pours[1]->createdAt->format('Y-m-d H:i:s'));
    }

    public function testDoesNotSavePourWhenNoTankCanHoldIt(): void
    {
        $this->expectException(NoSpaceException::class);

        try {
            $this->repository->addPour('Иван', 301, new DateTimeImmutable('2026-10-31 19:00:00'));
        } finally {
            assertSame([], $this->repository->pours());
            assertSame(0, $this->repository->tanks()[0]->currentLiters);
        }
    }

    public function testRejectsBlankName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            $this->repository->addPour('   ', 10, new DateTimeImmutable('2026-10-31 19:00:00'));
        } finally {
            assertSame([], $this->repository->pours());
        }
    }

    public function testUsesTankCapacityOf200LitersFromTheDatabase(): void
    {
        $this->db->createCommand()->update('tank', ['capacity' => 200], ['id' => 1])->execute();

        $pour = $this->repository->addPour('Иван', 250, new DateTimeImmutable('2026-10-31 19:00:00'));

        assertSame(2, $pour->tankId);
        assertSame(200, $this->repository->tanks()[0]->capacity);
        assertSame(0, $this->repository->tanks()[0]->currentLiters);
        assertSame(250, $this->repository->tanks()[1]->currentLiters);
    }
}
