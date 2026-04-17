<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'super_admin' => SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldReturnJson = static function (Request $request): bool {
            return $request->is('api/*') || $request->expectsJson();
        };

        $exceptions->shouldRenderJsonWhen($shouldReturnJson);

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], $exception->status);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request): Response {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return redirect()->guest(route('login'));
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'This action is unauthorized.',
                'errors' => null,
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => null,
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            $status = $exception->getStatusCode();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: (Response::$statusTexts[$status] ?? 'Request failed.'),
                'errors' => null,
            ], $status);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $exception->getMessage() : 'Server error.',
                'errors' => null,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
