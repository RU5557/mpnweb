<?php

use App\Http\Middleware\AdminAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.auth' => AdminAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Custom Penanganan Error Global - Direct Download Log & Custom View
        $exceptions->render(function (Throwable $e, Request $request) {

            // Tangkap semua error pada request halaman web/HTML
            if ($request->isMethod('GET') || $request->acceptsHtml()) {

                // 1. Ambil Detail Error
                $timestamp = now()->format('Y-m-d H:i:s');
                $errorMsg = $e->getMessage();
                $file = $e->getFile();
                $line = $e->getLine();
                $url = $request->fullUrl();
                $trace = $e->getTraceAsString();

                // 2. Format Teks Log
                $logContent = "=========================================\n";
                $logContent .= "   MPNWEB SYSTEM ERROR LOG\n";
                $logContent .= "=========================================\n";
                $logContent .= "Timestamp : {$timestamp}\n";
                $logContent .= "URL       : {$url}\n";
                $logContent .= "File      : {$file} (Line {$line})\n";
                $logContent .= "Message   : {$errorMsg}\n";
                $logContent .= "-----------------------------------------\n";
                $logContent .= "STACK TRACE:\n";
                $logContent .= $trace."\n";

                // Encode log ke Base64 agar bisa diunduh secara langsung via browser tanpa simpan di server
                $logBase64 = base64_encode($logContent);

                // 3. Tampilkan Halaman Error Kustom dengan data log
                return response()->view('errors.500', [
                    'timestamp' => $timestamp,
                    'logBase64' => $logBase64,
                    'logFileName' => 'Error_Log_MPNWEB_'.now()->format('Ymd_His').'.txt',
                ], 500);
            }
        });

    })->create();
