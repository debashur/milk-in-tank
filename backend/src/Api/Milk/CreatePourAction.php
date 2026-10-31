<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Api\Shared\ResponseFactory;
use App\Milk\MilkRepository;
use App\Milk\NoSpaceException;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

final class CreatePourAction
{
    public function __construct(
        private readonly MilkRepository $repository,
        private readonly ResponseFactory $responseFactory,
    ) {}

    public function __invoke(CreatePourInput $input): ResponseInterface
    {
        try {
            $pour = $this->repository->addPour($input->personName, $input->liters, new DateTimeImmutable());
        } catch (NoSpaceException | InvalidArgumentException $exception) {
            return $this->responseFactory->fail($exception->getMessage(), httpCode: Status::UNPROCESSABLE_ENTITY);
        }

        return $this->responseFactory
            ->success([
                'pour' => MilkData::pour($pour),
                'tanks' => MilkData::tanks($this->repository->tanks()),
            ])
            ->withStatus(Status::CREATED);
    }
}
