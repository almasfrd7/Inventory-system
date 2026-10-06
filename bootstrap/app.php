<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
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
        ==================================================
        API ERROR RESPONSES
        ==================================================

        All API errors are returned in the same shape as
        successful responses:

        { success: false, data: null, message: "...", errors?: {...} }
        */

        $exceptions->render(function (\Throwable $e, $request) {

            // Only intercept JSON (API) requests.
            if (!$request->expectsJson()) {
                return null;
            }

            // Validation errors: 422 with all field errors.
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => $e->getMessage(),
                    'errors' => $e->validator->errors()->toArray(),
                ], $e->status);
            }

            // Model not found: 404.
            // Route model binding throws ModelNotFoundException,
            // which Laravel converts to NotFoundHttpException.
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Resource not found.',
                ], 404);
            }

            // Any other exception: 500.
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'An internal server error occurred.',
            ], 500);
        });

    })->create();
