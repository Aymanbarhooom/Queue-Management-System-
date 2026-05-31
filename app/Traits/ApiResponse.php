<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Unified Response Structure
     */
    protected function apiResponse($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status_code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function apiError(string $message, int $code = 400): JsonResponse
    {
        return $this->apiResponse(null, $message, $code);
    }
}
