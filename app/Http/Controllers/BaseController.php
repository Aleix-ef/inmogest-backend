<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    use AuthorizesRequests;

    protected function sendResponse(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    protected function forbidden(): JsonResponse
    {
        return response()->json(['message' => 'Forbidden'], 403);
    }
}
