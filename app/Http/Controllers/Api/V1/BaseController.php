<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * Return an error response.
     *
     * @param int $code
     * @param string $status
     * @param string $error
     * @param mixed $errorMessages
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($code = 404, $error = 'Error', $errorMessages = [])
    {
        $response = [
            'timestamp' => now()->toIso8601String(),
            'code' => $code,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a success response.
     *
     * @param int $code
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSuccess($code = 200, $data, $message = 'SUCCESS_GET_DATA')
    {
        $response = [
            'timestamp' => now()->toIso8601String(),
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }
}
