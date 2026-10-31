<?php

declare(strict_types=1);

use App\Api;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Router\Route;
use Yiisoft\Swagger\Action\SwaggerJson;
use Yiisoft\Swagger\Action\SwaggerUi;

/**
 * @var array $params
 */

return [
    Route::get('/')->action(Api\IndexAction::class)->name('app/index'),
    Route::get('/tanks')->action(Api\Milk\ListTanksAction::class)->name('milk/tanks'),
    Route::get('/pours')->action(Api\Milk\ListPoursAction::class)->name('milk/pours'),
    Route::post('/pours')->action(Api\Milk\CreatePourAction::class)->name('milk/pours/create'),
    // Страница Swagger отдаёт HTML, спецификация — JSON.
    // Общий обработчик ответов иначе упаковал бы и страницу в JSON.
    Route::get('/swagger')
        ->middleware(FormatDataResponseAsHtml::class)
        ->action(static fn (SwaggerUi $swaggerUi) => $swaggerUi->withJsonUrl('/swagger/openapi.json'))
        ->name('swagger/ui'),
    Route::get('/swagger/openapi.json')
        ->middleware(Api\Swagger\IgnoreVendorDeprecations::class)
        ->middleware(FormatDataResponseAsJson::class)
        ->action(SwaggerJson::class)
        ->name('swagger/json'),
];
