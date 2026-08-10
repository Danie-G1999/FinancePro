<?php

namespace App\Shared\Http;

use App\Shared\Responses\ApiResponse;
use Illuminate\Routing\Controller;

abstract class BaseApiController extends Controller
{
    protected function success(
        mixed $data = null,
        string $message = 'Operación realizada correctamente',
        int $status = 200
    ) {
        return ApiResponse::success(
            $data,
            $message,
            $status
        );
    }

    protected function error(
        string $message,
        mixed $errors = null,
        int $status = 400
    ) {
        return ApiResponse::error(
            $message,
            $errors,
            $status
        );
    }
}
