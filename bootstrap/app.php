<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function ($middleware) {
    $middleware->alias([
        'kasir.autologout' => \App\Http\Middleware\ForceLogoutKasirAtOneAM::class,
        'owner.only' => \App\Http\Middleware\EnsureOwner::class,

    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
