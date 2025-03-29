<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya berjalan dalam mode debug
        if (!config('app.debug')) {
            return $next($request);
        }
        
        // Catat waktu mulai dan penggunaan memori
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Lanjutkan dengan request
        $response = $next($request);
        
        // Hitung penggunaan waktu dan memori
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $executionTime = round(($endTime - $startTime) * 1000, 2); // dalam ms
        $memoryUsage = round((($endMemory - $startMemory) / 1024 / 1024), 2); // dalam MB
        
        // Log jika eksekusi membutuhkan waktu lebih dari 500ms atau memori lebih dari 10MB
        if ($executionTime > 500 || $memoryUsage > 10) {
            Log::channel('performance')->info('Performance Alert', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'memory_used' => $memoryUsage . 'MB',
                'user_id' => $request->user() ? $request->user()->id : 'guest',
            ]);
            
            // Tambahkan header X-Performance ke response
            $response->headers->set('X-Performance-Time', $executionTime . 'ms');
            $response->headers->set('X-Performance-Memory', $memoryUsage . 'MB');
        }
        
        return $response;
    }
} 