<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Milk\TankState;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * Тело POST /pours: кто залил и сколько литров.
 */
#[FromBody]
final class CreatePourInput extends AbstractInput
{
    #[Required]
    #[Length(min: 1, max: 100)]
    public string $personName = '';

    #[Required]
    #[Integer(min: 1, max: TankState::MAX_CAPACITY)]
    public int $liters = 0;
}
