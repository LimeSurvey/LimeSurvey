<?php

declare(strict_types=1);

use Yiisoft\ErrorHandler\Renderer\JsonRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

/**
 * @var array $params
 */

return [
    ThrowableRendererInterface::class => JsonRenderer::class,
];
