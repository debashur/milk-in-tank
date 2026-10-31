<?php

declare(strict_types=1);

namespace App\Milk;

use DateTimeImmutable;
use InvalidArgumentException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;

/**
 * Читает цистерны и журнал из базы и записывает новую заливку.
 */
final class MilkRepository
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly TankAllocator $allocator,
    ) {}

    /**
     * @return list<TankState>
     */
    public function tanks(): array
    {
        $rows = $this->db
            ->select([
                'tank.id',
                'tank.capacity',
                'current_liters' => new Expression('COALESCE(SUM(pour.liters), 0)'),
            ])
            ->from('tank')
            ->leftJoin('pour', '{{pour}}.[[tank_id]] = {{tank}}.[[id]]')
            ->groupBy(['tank.id', 'tank.capacity'])
            ->orderBy(['tank.id' => SORT_ASC])
            ->all();

        return array_map($this->tank(...), $rows);
    }

    /**
     * Журнал заливок, новые сверху. В каждой строке объём её цистерны на текущий момент.
     *
     * @return list<Pour>
     */
    public function pours(): array
    {
        $currentLitersByTankId = [];
        foreach ($this->tanks() as $tank) {
            $currentLitersByTankId[$tank->id] = $tank->currentLiters;
        }

        $rows = $this->db
            ->select(['id', 'person_name', 'liters', 'tank_id', 'created_at'])
            ->from('pour')
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return array_map(
            fn(array $row): Pour => new Pour(
                id: (int) $row['id'],
                personName: (string) $row['person_name'],
                liters: (int) $row['liters'],
                tankId: (int) $row['tank_id'],
                tankCurrentLiters: $currentLitersByTankId[(int) $row['tank_id']],
                createdAt: new DateTimeImmutable((string) $row['created_at']),
            ),
            $rows,
        );
    }

    /**
     * Выбирает цистерну и записывает заливку в одной транзакции.
     * Если цистерна не нашлась, база остаётся как была.
     */
    public function addPour(string $personName, int $liters, DateTimeImmutable $createdAt): Pour
    {
        $personName = $this->personName($personName);

        return $this->db->transaction(function () use ($personName, $liters, $createdAt): Pour {
            $tankId = $this->allocator->chooseTank($this->tanks(), $liters);

            $this->db->createCommand()->insert('pour', [
                'person_name' => $personName,
                'liters' => $liters,
                'tank_id' => $tankId,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
            ])->execute();

            $id = (int) $this->db->getLastInsertId();
            $tankCurrentLiters = 0;
            foreach ($this->tanks() as $tank) {
                if ($tank->id === $tankId) {
                    $tankCurrentLiters = $tank->currentLiters;
                    break;
                }
            }

            return new Pour(
                id: $id,
                personName: $personName,
                liters: $liters,
                tankId: $tankId,
                tankCurrentLiters: $tankCurrentLiters,
                createdAt: $createdAt,
            );
        });
    }

    /**
     * @param array<string, mixed> $row
     */
    private function tank(array $row): TankState
    {
        return new TankState(
            id: (int) $row['id'],
            capacity: (int) $row['capacity'],
            currentLiters: (int) $row['current_liters'],
        );
    }

    private function personName(string $personName): string
    {
        $personName = trim($personName);
        $length = mb_strlen($personName);

        if ($length < 1 || $length > 100) {
            throw new InvalidArgumentException('Имя должно быть от 1 до 100 символов.');
        }

        return $personName;
    }
}
