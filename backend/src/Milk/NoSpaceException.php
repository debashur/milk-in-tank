<?php

declare(strict_types=1);

namespace App\Milk;

use RuntimeException;

use function sprintf;

final class NoSpaceException extends RuntimeException
{
    public function __construct(
        public readonly int $liters,
        public readonly int $maxFreeLiters,
        public readonly int $tankId,
    ) {
        parent::__construct(sprintf(
            'Ни одна цистерна не вмещает %d л. Максимум свободного места — %d л в цистерне %d.',
            $liters,
            $maxFreeLiters,
            $tankId,
        ));
    }
}
