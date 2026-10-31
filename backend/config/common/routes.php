<?php

declare(strict_types=1);

use App\Api;
use Yiisoft\Router\Route;

/**
 * @var array $params
 */

return [
    Route::get('/')->action(Api\IndexAction::class)->name('app/index'),
    Route::get('/tanks')->action(Api\Milk\ListTanksAction::class)->name('milk/tanks'),
    Route::get('/pours')->action(Api\Milk\ListPoursAction::class)->name('milk/pours'),
    Route::post('/pours')->action(Api\Milk\CreatePourAction::class)->name('milk/pours/create'),
];
