<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    public function sendResponse($result, $title = "", $isMessage = false, $data = [])
    {


        $response = [
            'success' => true,
            'message' => $result
        ];


        if (!empty($title)) {
            $response['title'] = $title;
        }


        if (isset($data) && !empty($data) && count($data) > 0) {
            $response['data'] = $data;
        }
        if (!$isMessage) {
            if (!self::isJson($result)) {
                $result = json_decode($result, true);
            }
            return response()->json($result, 200);
        } else {
            return response()->json($response, 200);
        }
    }

    function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }



    public function sendError($error, $errorMessages = [], $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function isEmptyOrNull($element)
    {
        if (is_array($element)) {
            return isset($element) && $element != null && !empty($element) && (count($element) > 0) && $element;
        } else {
            return isset($element) && $element != null && !empty($element) && $element;
        }
    }
}
