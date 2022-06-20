<?php

use Yiisoft\Router\Route;
use App\Middleware\AuthMiddleware;
use App\PoC\PoCController;

return [
    Route::get('/validate')
        ->middleware(AuthMiddleware::class)
        ->action([PoCController::class, 'validateToken'])
        ->name('POC Token Validation')
];