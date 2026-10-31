<?php

declare(strict_types=1);

namespace App\Api\Swagger;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PHP 8.5 помечает устаревшими куски внутри библиотек Swagger.
 * Обработчик ошибок Yii превращает такое предупреждение в ответ 500.
 * Здесь они глушатся только на время сборки спецификации и только для файлов из vendor.
 */
final class IgnoreVendorDeprecations implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        set_error_handler(static function (int $severity, string $message, string $file): bool {
            if (
                ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED)
                && str_contains($file, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)
            ) {
                return true;
            }

            return false;
        });

        try {
            return $handler->handle($request);
        } finally {
            restore_error_handler();
        }
    }
}
