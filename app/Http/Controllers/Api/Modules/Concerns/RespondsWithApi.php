<?php

namespace App\Http\Controllers\Api\Modules\Concerns;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait RespondsWithApi
{
    protected function success(mixed $data = null, string $message = 'OK', int $code = 200)
    {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->additional([
                'status' => 'success',
                'message' => $message,
            ])->response()->setStatusCode($code);
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(string $message, int $code)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
