<?php

namespace App\Http\Responses;

use App\Enums\ResponseStatus;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a success JSON response.
     */
    public static function success(
        mixed $data = [],
        string $message = '',
        int $statusCode = 200,
        ?string $token = null,
    ): JsonResponse {
        return self::build(ResponseStatus::Success, $message, $data, $statusCode, $token);
    }

    /**
     * Return an error JSON response.
     */
    public static function error(
        string $message = '',
        int $statusCode = 400,
        mixed $data = [],
    ): JsonResponse {
        return self::build(ResponseStatus::Error, $message, $data, $statusCode);
    }

    /**
     * 201 Created.
     */
    public static function created(mixed $data = [], string $message = 'Resource created successfully.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * 404 Not Found.
     */
    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * 401 Unauthorized.
     */
    public static function unauthorized(string $message = 'Unauthorized.'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * 403 Forbidden.
     */
    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * 422 Unprocessable Entity.
     */
    public static function unprocessable(string $message = 'Validation failed.', mixed $data = []): JsonResponse
    {
        return self::error($message, 422, $data);
    }

    /**
     * 500 Internal Server Error.
     */
    public static function serverError(string $message = 'Internal server error.'): JsonResponse
    {
        return self::error($message, 500);
    }

    /**
     * Core builder — all static helpers delegate here.
     */
    private static function build(
        ResponseStatus $status,
        string $message,
        mixed $data,
        int $statusCode,
        ?string $token = null,
    ): JsonResponse {
        $body = [
            'status' => $status->toBool(),
            'message' => $message !== '' ? $message : $status->defaultMessage(),
            'data' => $data,
            'statusCode' => $statusCode,
        ];

        if ($token !== null) {
            $body['token'] = $token;
        }

        return response()->json($body, $statusCode);
    }
}
