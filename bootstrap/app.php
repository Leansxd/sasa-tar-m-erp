<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            $status = 500;
            $message = 'Beklenmeyen bir hata oluştu.';

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: match ($status) {
                    403 => 'Bu işlemi gerçekleştirmek için yetkiniz bulunmuyor.',
                    404 => 'Aradığınız sayfa veya kayıt bulunamadı.',
                    419 => 'Oturum süreniz doldu, lütfen sayfayı yenileyiniz.',
                    503 => 'Sistem bakım modundadır.',
                    default => 'İşlem gerçekleştirilemedi.'
                };
            } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404;
                $message = 'İlgili veri kaydı bulunamadı.';
            } elseif ($e instanceof \Illuminate\Database\QueryException) {
                $status = 500;
                $errorCode = $e->errorInfo[1] ?? null;
                $message = match ($errorCode) {
                    1062 => 'Bu kayıt veya benzersiz bilgi sistemde zaten mevcut.',
                    1451, 1452 => 'Bu kayıt diğer ilişkili verilerle bağlantılı olduğu için işlem tamamlanamadı.',
                    default => 'Veritabanı işleminde bir hata meydana geldi.'
                };
            } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                $status = 403;
                $message = $e->getMessage() ?: 'Bu işlemi gerçekleştirmek için yetkiniz bulunmuyor.';
            } else {
                $message = $e->getMessage() ?: 'Sistemde bir hata oluştu.';
            }

            if (!$request->isMethod('GET') && $request->header('X-Inertia')) {
                return redirect()->back()->with('error', $message);
            }

            if ($request->header('X-Inertia')) {
                return inertia('Error', [
                    'status' => $status,
                    'message' => $message,
                    'details' => config('app.debug') ? $e->getMessage() : null,
                ])->toResponse($request)->setStatusCode($status);
            }

            return null;
        });
    })->create();
