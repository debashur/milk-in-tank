<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Milk\Pour;
use App\Milk\TankState;

/**
 * Поля JSON для цистерны и строки журнала.
 */
final class MilkData
{
    public static function tank(TankState $tank): array
    {
        return [
            'id' => $tank->id,
            'capacity' => $tank->capacity,
            'currentLiters' => $tank->currentLiters,
            'freeLiters' => $tank->capacity - $tank->currentLiters,
        ];
    }

    /**
     * @param list<TankState> $tanks
     * @return list<array<string, int>>
     */
    public static function tanks(array $tanks): array
    {
        return array_map(self::tank(...), $tanks);
    }

    public static function pour(Pour $pour): array
    {
        return [
            'id' => $pour->id,
            'personName' => $pour->personName,
            'liters' => $pour->liters,
            'tankId' => $pour->tankId,
            'tankCurrentLiters' => $pour->tankCurrentLiters,
            'createdAt' => $pour->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param list<Pour> $pours
     * @return list<array<string, int|string>>
     */
    public static function pours(array $pours): array
    {
        return array_map(self::pour(...), $pours);
    }
}
