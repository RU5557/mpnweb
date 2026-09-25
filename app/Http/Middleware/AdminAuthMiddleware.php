<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! session('is_admin')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses Panel Admin.');
        }

        return $next($request);
    }
}
