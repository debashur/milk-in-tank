<?php

declare(strict_types=1);

namespace App\Api\Milk;

use App\Api\Shared\ResponseFactory;
use App\Milk\MilkRepository;
use App\Milk\NoSpaceException;
use DateTimeImmutable;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

#[OA\Post(
    path: '/pours',
    operationId: 'createPour',
    summary: 'Залить молоко',
    description: 'Порция целиком попадает в одну цистерну. Сервер выбирает ту, куда она влезает и где молока меньше всего.',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CreatePourRequest'),
    ),
    tags: ['Молоко'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Заливка записана. В ответе сама заливка и свежий список цистерн.',
            content: new OA\JsonContent(ref: '#/components/schemas/CreatePourResponse'),
        ),
        new OA\Response(
            response: 422,
            description: 'Порция никуда не влезла или поля запроса неверные. В журнал ничего не пишется.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
        ),
    ],
)]
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
