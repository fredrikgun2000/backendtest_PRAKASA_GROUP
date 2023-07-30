<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;

class ApiResponse
{
    public static function success($data, $message = null,  $meta = null, $statusCode = 200)
    {
        $api = array(
            'success' => true,
            'data' => $data,
            'message' => $message,
        );

        if ($meta != null) {
            $api['meta'] =  array(
                'pagination' => array(
                    'per_page' => $meta->perPage(),
                    'current_page' => $meta->currentPage(),
                    'last_page' => $meta->lastPage(),
                ),
            );
        }
        return response()->json($api, $statusCode);
    }

    public static function error($message, $statusCode)
    {
        $api = array(
            'success' => false,
            'message' => $message,
        );
        return response()->json($api, $statusCode);
    }
}
