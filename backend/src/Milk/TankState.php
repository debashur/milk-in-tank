<?php

declare(strict_types=1);

namespace App\Milk;

use InvalidArgumentException;

/**
 * Одна цистерна так, как она сейчас есть в базе: номер, вместимость и сколько уже залито.
 */
final readonly class TankState
{
    public const MAX_CAPACITY = 300;

    public function __construct(
        public int $id,
        public int $capacity,
        public int $currentLiters,
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('Номер цистерны должен быть больше нуля.');
        }

        if ($capacity < 1 || $capacity > self::MAX_CAPACITY) {
            throw new InvalidArgumentException(
                "Вместимость цистерны $id должна быть от 1 до " . self::MAX_CAPACITY . ' л.',
            );
        }

        if ($currentLiters < 0 || $currentLiters > $capacity) {
            throw new InvalidArgumentException("Некорректный объём цистерны $id.");
        }
    }
}
