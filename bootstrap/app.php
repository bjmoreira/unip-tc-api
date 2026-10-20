<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Registra os comandos de app/Console/Commands. Informar o arquivo de rotas
    // de console em withRouting nao inclui esse diretorio, e sem esta chamada o
    // comando de simulacao nao apareceria no artisan.
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        // O front roda em outra origem (Vite, porta 5173), por isso o CORS.
        $middleware->validateCsrfTokens(except: ['api/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
