<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekLoginLab
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        if (!session('userlab_id')) {

            return redirect()
                ->route('lab.login')
                ->with(
                    'error',
                    'Silakan login terlebih dahulu.'
                );
        }

        return $next($request);
    }
}