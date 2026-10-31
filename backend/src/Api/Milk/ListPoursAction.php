<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Api\Shared\ResponseFactory;
use App\Milk\MilkRepository;
use Psr\Http\Message\ResponseInterface;

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
