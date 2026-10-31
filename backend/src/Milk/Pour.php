<?php

declare(strict_types=1);

namespace App\Milk;

use DateTimeImmutable;

/**
 * Одна заливка в журнале и сколько молока в её цистерне сейчас.
 */
final readonly class Pour
{
    public function __construct(
        public int $id,
        public string $personName,
        public int $liters,
        public int $tankId,
        public int $tankCurrentLiters,
        public DateTimeImmutable $createdAt,
    ) {}
}
