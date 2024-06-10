<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SendResponseController extends Controller
{

    public function sendSuccess($data, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    public function sendError($errorMessages = [], $code = 500)
    {
        $response = [
            'success' => false,
            'data' => 'null',
            'message' => $errorMessages,
        ];
        return response()->json($response, $code);
    }
}
