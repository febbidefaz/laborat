<?php

use App\Http\Middleware\CekLoginLab;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {

            Route::middleware(['web', 'cekloginlab'])
                ->prefix('rawat-inap')
                ->name('rawatinap.')
                ->group(base_path('routes/rawatinap.php'));

            Route::middleware(['web', 'cekloginlab'])
                ->prefix('lain')
                ->name('lain.')
                ->group(base_path('routes/lain.php'));

            Route::middleware(['web', 'cekloginlab'])
                ->prefix('lab')
                ->name('lab.')
                ->group(base_path('routes/lab.php'));

            Route::middleware('web')
                ->prefix('userlab')
                ->group(base_path('routes/userlab.php'));

            Route::middleware(['web', 'cekloginlab'])
                ->prefix('pulang')
                ->name('pulang.')
                ->group(base_path('routes/pulang.php'));

            Route::middleware(['web', 'cekloginlab'])
                ->prefix('rawat-jalan')
                ->name('rawatjalan.')
                ->group(base_path('routes/rawatjalan.php'));

            Route::middleware(['web', 'cekloginlab'])
                ->prefix('igd')
                ->name('igd.')
                ->group(base_path('routes/igd.php'));
            
            Route::middleware(['web', 'cekloginlab'])
                ->prefix('notifsp')
                ->name('notifsp.')
                ->group(base_path('routes/notifsp.php'));
            
            Route::middleware(['web', 'cekloginlab'])
                ->prefix('sp')
                ->name('sp.')
                ->group(base_path('routes/sp.php'));
        },
    )

    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'cekloginlab' => CekLoginLab::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();