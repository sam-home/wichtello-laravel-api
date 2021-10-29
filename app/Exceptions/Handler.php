<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (AccessDeniedHttpException $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]], 403);
        });

        $this->renderable(function (UnauthorizedHttpException $e) {
            return response()->json(['error' => ['message' => 'Ungültige Zugangsdaten']], 401);
        });

        $this->renderable(function (ValidationException $e) {
            return response()->json(['error' => ['data' => $e->errors()]], 422);
        });
    }
}
