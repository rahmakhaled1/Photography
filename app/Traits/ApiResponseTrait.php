<?php
namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse(
        $data = null,
        string $message = 'Success',
        int $code = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ], $code);
    }

    public function errorResponse(
        string $message = 'Error',
        int $code = 400,
        $errors = null
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'code'    => $code,
        ], $code);
    }
}
