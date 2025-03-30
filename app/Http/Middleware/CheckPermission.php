<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!$request->user()) {
            abort(403, 'Anda harus login untuk mengakses halaman ini.');
        }

        // Jika tidak ada permission yang diberikan, lanjutkan request
        if (empty($permissions)) {
            return $next($request);
        }

        // Jika user adalah admin, bypass semua permission check
        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        // Periksa apakah user memiliki salah satu dari permission yang diberikan
        if ($request->user()->hasAnyPermission($permissions)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
} 