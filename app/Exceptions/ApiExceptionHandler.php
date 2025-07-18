<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function handle(Throwable $exception, Request $request): JsonResponse {
        // Authentication Exception
        if ($exception instanceof AuthenticationException) {
            return ApiResponse::unauthorized('Authentication required. Please login to continue.');
        }

        // Authorization Exception
        if ($exception instanceof AuthorizationException) {
            return ApiResponse::forbidden('You do not have permission to perform this action.');
        }

        // Validation Exception
        if ($exception instanceof ValidationException) {
            return ApiResponse::validationError(
                $exception->errors(),
                'The given data was invalid.'
            );
        }

        // Model Not Found Exception
        if ($exception instanceof ModelNotFoundException) {
            $model = class_basename($exception->getModel());
            return ApiResponse::notFound("The requested {$model} was not found.");
        }

        // Not Found HTTP Exception
        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::notFound('The requested resource was not found.');
        }

        // Method Not Allowed Exception
        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                'The HTTP method is not allowed for this route.',
                405
            );
        }

        // Database Query Exception
        if ($exception instanceof QueryException) {
            // Log the actual error for debugging
            Log::error('Database Query Exception: '.$exception->getMessage(), [
                'sql' => $exception->getSql(),
                'bindings' => $exception->getBindings(),
                'trace' => $exception->getTraceAsString()
            ]);

            // Check for specific database errors
            $errorCode = $exception->errorInfo[1] ?? null;

            switch ($errorCode) {
                case 1062: // Duplicate entry
                    return ApiResponse::error(
                        'A record with this information already exists.',
                        422
                    );
                case 1451: // Foreign key constraint fails (delete)
                    return ApiResponse::error(
                        'Cannot delete this record as it is being used by other records.',
                        422
                    );
                case 1452: // Foreign key constraint fails (insert/update)
                    return ApiResponse::error(
                        'The referenced record does not exist.',
                        422
                    );
                default:
                    return ApiResponse::serverError('A database error occurred. Please try again.');
            }
        }

        // HTTP Exception
        if ($exception instanceof HttpException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'An HTTP error occurred.',
                $exception->getStatusCode()
            );
        }

        // Log unexpected exceptions
        Log::error('Unexpected API Exception: '.$exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Generic server error for production
        if (app()->environment('production')) {
            return ApiResponse::serverError('An unexpected error occurred. Please try again later.');
        }

        // Detailed error for development
        return ApiResponse::serverError(
            'Development Error: '.$exception->getMessage().' in '.$exception->getFile().':'.$exception->getLine()
        );
    }
}
