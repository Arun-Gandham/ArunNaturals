<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ], $status);
    }

    public static function error(
        string $message = 'Something went wrong',
        int $status = 500,
        mixed $data = null,
        array $errors = [],
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
            'meta'    => $meta,
        ], $status);
    }
}