<?php

declare(strict_types=1);

namespace App\Milk;

use InvalidArgumentException;

final class TankAllocator
{
    /**
     * Среди переданных цистерн, куда объём помещается целиком, выбирает самую пустую.
     * Если литров поровну, берётся меньший номер.
     *
     * @param list<TankState> $tanks строки из базы, без заранее известного числа цистерн
     */
    public function chooseTank(array $tanks, int $liters): int
    {
        if ($liters < 1) {
            throw new InvalidArgumentException('Количество молока должно быть целым числом от 1.');
        }

        if ($tanks === []) {
            throw new InvalidArgumentException('Нет цистерн.');
        }

        $best = null;
        $maxFree = -1;
        $maxFreeTankId = 0;
        $seenIds = [];

        foreach ($tanks as $tank) {
            if (isset($seenIds[$tank->id])) {
                throw new InvalidArgumentException("Цистерна $tank->id передана дважды.");
            }
            $seenIds[$tank->id] = true;

            $free = $tank->capacity - $tank->currentLiters;
            if ($free > $maxFree || ($free === $maxFree && $tank->id < $maxFreeTankId)) {
                $maxFree = $free;
                $maxFreeTankId = $tank->id;
            }

            if ($free < $liters) {
                continue;
            }

            if ($best === null || $this->isEmptier($tank, $best)) {
                $best = $tank;
            }
        }

        if ($best === null) {
            throw new NoSpaceException($liters, $maxFree, $maxFreeTankId);
        }

        return $best->id;
    }

    /**
     * Кандидат пустее уже выбранной цистерны: в нём меньше литров.
     * Если литров поровну, пустее считается цистерна с меньшим номером.
     */
    private function isEmptier(TankState $candidate, TankState $current): bool
    {
        if ($candidate->currentLiters !== $current->currentLiters) {
            return $candidate->currentLiters < $current->currentLiters;
        }

        return $candidate->id < $current->id;
    }
}
