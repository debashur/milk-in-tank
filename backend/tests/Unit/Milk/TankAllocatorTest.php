<?php

declare(strict_types=1);

namespace App\Tests\Unit\Milk;

use App\Milk\NoSpaceException;
use App\Milk\TankAllocator;
use App\Milk\TankState;
use Codeception\Test\Unit;
use InvalidArgumentException;

use function PHPUnit\Framework\assertSame;

final class TankAllocatorTest extends Unit
{
    private TankAllocator $allocator;

    protected function _before(): void
    {
        $this->allocator = new TankAllocator();
    }

    public function testPoursIntoTheFirstTankWhenAllAreEmpty(): void
    {
        assertSame(1, $this->allocator->chooseTank($this->levels(0, 0, 0, 0, 0), 100));
    }

    public function testPoursIntoTheEmptiestTank(): void
    {
        assertSame(2, $this->allocator->chooseTank($this->levels(100, 0, 0, 0, 0), 100));
    }

    public function testBreaksTieBySmallestTankNumber(): void
    {
        assertSame(1, $this->allocator->chooseTank($this->levels(100, 100, 100, 100, 100), 50));
    }

    public function testSkipsTanksThatCannotHoldTheWholePour(): void
    {
        assertSame(4, $this->allocator->chooseTank($this->levels(100, 100, 50, 0, 0), 280));
    }

    public function testRejectsPourThatFitsNowhere(): void
    {
        $this->expectException(NoSpaceException::class);
        $this->expectExceptionMessage(
            'Ни одна цистерна не вмещает 150 л. Максимум свободного места — 100 л в цистерне 1.',
        );

        $this->allocator->chooseTank($this->levels(200, 250, 280, 290, 300), 150);
    }

    public function testAcceptsPourThatFillsAnEmptyTankTo300(): void
    {
        assertSame(1, $this->allocator->chooseTank($this->levels(0, 0, 0, 0, 0), TankState::MAX_CAPACITY));
    }

    public function testSkipsTankWhenPourWouldExceed300(): void
    {
        assertSame(2, $this->allocator->chooseTank($this->levels(1, 0, 0, 0, 0), TankState::MAX_CAPACITY));
    }

    public function testRejectsPourAbove300EvenWhenTanksAreEmpty(): void
    {
        $this->expectException(NoSpaceException::class);
        $this->expectExceptionMessage(
            'Ни одна цистерна не вмещает 301 л. Максимум свободного места — 300 л в цистерне 1.',
        );

        $this->allocator->chooseTank($this->levels(0, 0, 0, 0, 0), TankState::MAX_CAPACITY + 1);
    }

    public function testRejectsNonPositiveAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->allocator->chooseTank($this->levels(0, 0, 0, 0, 0), 0);
    }

    public function testRejectsCapacityAbove300(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Вместимость цистерны 1 должна быть от 1 до 300 л.');

        new TankState(id: 1, capacity: TankState::MAX_CAPACITY + 1, currentLiters: 0);
    }

    public function testRejectsCurrentVolumeAbove300(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный объём цистерны 1.');

        new TankState(id: 1, capacity: TankState::MAX_CAPACITY, currentLiters: TankState::MAX_CAPACITY + 1);
    }

    public function testFillsTankOf200LitersToItsOwnCapacity(): void
    {
        $tanks = [
            new TankState(id: 1, capacity: 200, currentLiters: 0),
        ];

        assertSame(1, $this->allocator->chooseTank($tanks, 200));
    }

    public function testRejectsPourAboveTankOf200Liters(): void
    {
        $this->expectException(NoSpaceException::class);
        $this->expectExceptionMessage(
            'Ни одна цистерна не вмещает 201 л. Максимум свободного места — 200 л в цистерне 1.',
        );

        $this->allocator->chooseTank([
            new TankState(id: 1, capacity: 200, currentLiters: 0),
        ], 201);
    }

    public function testSkipsTankOf200WhenPourFitsOnlyInto300(): void
    {
        $tanks = [
            new TankState(id: 1, capacity: 200, currentLiters: 0),
            new TankState(id: 2, capacity: TankState::MAX_CAPACITY, currentLiters: 0),
        ];

        assertSame(2, $this->allocator->chooseTank($tanks, 250));
    }

    public function testPrefersEmptierTankOf200OverFullerTankOf300(): void
    {
        $tanks = [
            new TankState(id: 1, capacity: 200, currentLiters: 0),
            new TankState(id: 2, capacity: TankState::MAX_CAPACITY, currentLiters: 50),
        ];

        assertSame(1, $this->allocator->chooseTank($tanks, 150));
    }

    public function testRejectsCurrentVolumeAboveTankOf200Liters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный объём цистерны 1.');

        new TankState(id: 1, capacity: 200, currentLiters: 201);
    }

    public function testWorksForAnyNumberOfTanks(): void
    {
        $tanks = [
            new TankState(id: 7, capacity: TankState::MAX_CAPACITY, currentLiters: 0),
            new TankState(id: 8, capacity: TankState::MAX_CAPACITY, currentLiters: 0),
        ];

        assertSame(7, $this->allocator->chooseTank($tanks, 10));
    }

    /**
     * @return list<TankState>
     */
    private function levels(int $tank1, int $tank2, int $tank3, int $tank4, int $tank5): array
    {
        return [
            new TankState(1, TankState::MAX_CAPACITY, $tank1),
            new TankState(2, TankState::MAX_CAPACITY, $tank2),
            new TankState(3, TankState::MAX_CAPACITY, $tank3),
            new TankState(4, TankState::MAX_CAPACITY, $tank4),
            new TankState(5, TankState::MAX_CAPACITY, $tank5),
        ];
    }
}
