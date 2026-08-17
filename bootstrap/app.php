<?php

use App\Exceptions\BusinessRuleException;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('Dữ liệu không hợp lệ', $exception->errors(), 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('Chưa xác thực', status: 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('Không có quyền thực hiện thao tác này', status: 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('Không tìm thấy dữ liệu', status: 404);
        });

        $exceptions->render(function (BusinessRuleException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $exception->getMessage(),
                    $exception->errors(),
                    $exception->status(),
                );
            }

            if ($exception->errors() !== []) {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors($exception->errors());
            }

            return back()->with('error', $exception->getMessage());
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $message = match ($exception->getStatusCode()) {
                400 => 'Request không hợp lệ',
                401 => 'Chưa xác thực',
                403 => 'Không có quyền thực hiện thao tác này',
                404 => 'Không tìm thấy dữ liệu',
                405 => 'Phương thức không được hỗ trợ',
                419 => 'Phiên làm việc đã hết hạn',
                429 => 'Có quá nhiều yêu cầu',
                default => $exception->getStatusCode() >= 500
                    ? 'Có lỗi xảy ra'
                    : 'Không thể xử lý yêu cầu',
            };

            return ApiResponse::error($message, status: $exception->getStatusCode());
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiResponse::error('Có lỗi xảy ra', status: 500);
        });
    })->create();
