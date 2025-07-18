<?php /** @noinspection PhpClassConstantAccessedViaChildClassInspection */

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    /**
     * Validation error response
     */
    public static function validationError(
        $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'Operation failed',
        int $statusCode = Response::HTTP_BAD_REQUEST,
        $errors = null,
        $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse {
        return self::error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized access'): JsonResponse {
        return self::error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Access forbidden'): JsonResponse {
        return self::error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Server error response
     */
    public static function serverError(string $message = 'Internal server error'): JsonResponse {
        return self::error($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Created response
     */
    public static function created(
        $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Success response
     */
    public static function success(
        $data = null,
        string $message = 'Operation successful',
        int $statusCode = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Updated response
     */
    public static function updated(
        $data = null,
        string $message = 'Resource updated successfully'
    ): JsonResponse {
        return self::success($data, $message);
    }

    /**
     * Deleted response
     */
    public static function deleted(string $message = 'Resource deleted successfully'): JsonResponse {
        return self::success(null, $message);
    }

    /**
     * No content response
     */
    public static function noContent(): JsonResponse {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Paginated response
     */
    public static function paginated(
        $paginatedData,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        $data = $paginatedData->items();
        $meta = [
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
                'has_more_pages' => $paginatedData->hasMorePages(),
            ]
        ];

        return self::success($data, $message, Response::HTTP_OK, $meta);
    }

    /**
     * Collection response
     */
    public static function collection(
        $data,
        string $message = 'Data retrieved successfully',
        array $meta = []
    ): JsonResponse {
        return self::success($data, $message, Response::HTTP_OK, $meta);
    }
}
