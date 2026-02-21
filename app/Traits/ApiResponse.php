<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($message = "Success", $data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    protected function errorResponse($message = "Error", $errors = null, $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], $code);
    }
}