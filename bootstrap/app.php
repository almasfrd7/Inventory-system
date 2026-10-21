<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        All API errors use one shape:
        { success: false, data: null, message: "...", errors?: {...} }

        Exceptions are still reported (logged) as normal; only the
        response is customised here.
        */

        $isApi = fn (Request $request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $isApi($request)
        );

        $exceptions->render(function (Throwable $e, Request $request) use ($isApi) {

            if (! $isApi($request)) {
                return null;
            }

            $error = fn (string $message, int $status, array $extra = [], array $headers = []) => response()->json(
                array_merge(['success' => false, 'data' => null, 'message' => $message], $extra),
                $status,
                $headers
            );

            // 422 - validation
            if ($e instanceof ValidationException) {
                return $error($e->getMessage(), $e->status, [
                    'errors' => $e->validator->errors()->toArray(),
                ]);
            }

            // 401 - missing / invalid token
            if ($e instanceof AuthenticationException) {
                return $error('Unauthenticated.', 401);
            }

            // 404 - unknown route or model (don't leak model class names)
            if ($e instanceof NotFoundHttpException) {
                return $error('Resource not found.', 404);
            }

            // 403, 405, 429 ... keep the real status code
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                return $error(
                    $e->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error'),
                    $status,
                    [],
                    $e->getHeaders()
                );
            }

            // 500 - details only when APP_DEBUG=true
            return $error(
                config('app.debug') ? $e->getMessage() : 'An internal server error occurred.',
                500
            );
        });

    })->create();
