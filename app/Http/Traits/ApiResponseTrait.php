<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    protected function errorResponse(string $message = 'Error', $errors = null, int $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $statusCode);
    }
}
