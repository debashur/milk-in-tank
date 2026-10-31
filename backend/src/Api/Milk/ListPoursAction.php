<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Api\Shared\ResponseFactory;
use App\Milk\MilkRepository;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

#[OA\Get(
    path: '/pours',
    operationId: 'listPours',
    summary: 'Журнал заливок',
    description: 'Кто, сколько и в какую цистерну залил. Сверху последняя заливка. tankCurrentLiters — сколько в этой цистерне сейчас.',
    tags: ['Молоко'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Журнал, новые записи первыми',
            content: new OA\JsonContent(ref: '#/components/schemas/PourListResponse'),
        ),
    ],
)]
final class ListPoursAction
{
    public function __construct(
        private readonly MilkRepository $repository,
        private readonly ResponseFactory $responseFactory,
    ) {}

    public function __invoke(): ResponseInterface
    {
        return $this->responseFactory->success(MilkData::pours($this->repository->pours()));
    }
}
