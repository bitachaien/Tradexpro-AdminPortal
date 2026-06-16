<?php

use Illuminate\Http\JsonResponse;

function responseJsonData(bool $success, string $message = '', array|object|null $data = []): JsonResponse
{
    $message = !$success && empty($message) ? __('Something went wrong! Please try again later') : $message;
    return response()->json(['success' => $success, 'message' => $message, 'data' => $data]);
}
