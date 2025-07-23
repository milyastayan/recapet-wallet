<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    protected function errorResponse($message, $title = null, $code = Response::HTTP_BAD_REQUEST)
    {
        return response()->json(array_filter([
            'message' => $message,
            'title' => $title,
        ]), $code);
    }

    protected function successResponse($message, $title = null, $data = [], $code = Response::HTTP_OK)
    {
        $result['message']['body'] = $message;

        if ($title) {
            $result['message']['title'] = $title;
        }

        if ($data) {
            $result['data'] = $data;
        }

        return response()->json($result, $code);
    }

    protected function successDataResponse($data = [], $meta = null, $code = Response::HTTP_OK)
    {
        $result['data'] = $data;

        if ($meta) {
            $result['meta'] = $meta;
        }

        return response()->json($result, $code);
    }

    protected function errorDataResponse($message, $title = null, $data = [], $code = Response::HTTP_BAD_REQUEST)
    {
        $result['message'] = array_filter([
            'message' => $message,
            'title' => $title,
        ]);

        if ($data) {
            $result['data'] = $data;
        }

        return response()->json($result, $code);
    }

    protected function notFoundResponse($message, $title = null)
    {
        $result['message']['body'] = $message;

        if ($title) {
            $result['message']['title'] = $title;
        }

        return response()->json($result, Response::HTTP_NOT_FOUND);
    }
}
