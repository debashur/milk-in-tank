<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Api\Shared\ResponseFactory;
use App\Milk\MilkRepository;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

#[OA\Get(
    path: '/tanks',
    operationId: 'listTanks',
    summary: 'Список цистерн',
    description: 'Вместимость, текущий объём и свободное место каждой цистерны.',
    tags: ['Молоко'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Текущее состояние цистерн',
            content: new OA\JsonContent(ref: '#/components/schemas/TankListResponse'),
        ),
    ],
)]
final class ListTanksAction
{
    public function __construct(
        private readonly MilkRepository $repository,
        private readonly ResponseFactory $responseFactory,
    ) {}

    public function __invoke(): ResponseInterface
    {
        return $this->responseFactory->success(MilkData::tanks($this->repository->tanks()));
    }
}
