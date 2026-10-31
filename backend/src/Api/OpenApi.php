<?php

declare(strict_types=1);

namespace App\Api;

use OpenApi\Attributes as OA;

// Общее описание API для Swagger. Методы описаны атрибутами на классах действий.
#[OA\Info(
    version: '1.0',
    title: 'Учёт молока',
    description: 'Заливка молока в цистерны. Сервер сам выбирает цистерну: вся порция целиком в ту, где молока меньше всего. При равном объёме берётся цистерна с меньшим номером.',
)]
#[OA\Server(url: '/', description: 'Этот сервер')]
#[OA\Schema(
    schema: 'Tank',
    required: ['id', 'capacity', 'currentLiters', 'freeLiters'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'capacity', description: 'Вместимость цистерны, от 1 до 300 л', type: 'integer', example: 300),
        new OA\Property(property: 'currentLiters', description: 'Сколько молока сейчас', type: 'integer', example: 100),
        new OA\Property(property: 'freeLiters', description: 'Сколько ещё влезет', type: 'integer', example: 200),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'Pour',
    required: ['id', 'personName', 'liters', 'tankId', 'tankCurrentLiters', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'personName', type: 'string', example: 'Иван'),
        new OA\Property(property: 'liters', description: 'Сколько залили этой порцией', type: 'integer', example: 100),
        new OA\Property(property: 'tankId', description: 'В какую цистерну попала порция', type: 'integer', example: 1),
        new OA\Property(property: 'tankCurrentLiters', description: 'Сколько в этой цистерне сейчас, после всех заливок', type: 'integer', example: 100),
        new OA\Property(property: 'createdAt', type: 'string', example: '2026-10-31 18:00:00'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'TankListResponse',
    required: ['status', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Tank')),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PourListResponse',
    required: ['status', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Pour')),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CreatePourRequest',
    required: ['personName', 'liters'],
    properties: [
        new OA\Property(property: 'personName', type: 'string', maxLength: 100, minLength: 1, example: 'Иван'),
        new OA\Property(property: 'liters', description: 'Целая порция, от 1 до 300 л. В одну цистерну, без дробления.', type: 'integer', maximum: 300, minimum: 1, example: 100),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CreatePourResponse',
    required: ['status', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(
            property: 'data',
            required: ['pour', 'tanks'],
            properties: [
                new OA\Property(property: 'pour', ref: '#/components/schemas/Pour'),
                new OA\Property(property: 'tanks', type: 'array', items: new OA\Items(ref: '#/components/schemas/Tank')),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['status', 'error_message'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'failed'),
        new OA\Property(property: 'error_message', type: 'string', example: 'Ни одна цистерна не вмещает 250 л. Максимум свободного места — 200 л в цистерне 1.'),
        new OA\Property(property: 'error_data', description: 'Появляется, если не прошла проверка полей запроса', type: 'object', nullable: true),
    ],
    type: 'object',
)]
final class OpenApi
{
}
