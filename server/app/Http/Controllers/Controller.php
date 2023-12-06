<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Dataset to json response
     * @param $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function jsonResponse($data, string $message = null) : JsonResponse {

        // Set success
        $success = !!$data || (is_array($data) && sizeof($data) > 0);

        // Set success and failure message
        $successMessage = $message ?? "Aktion erfolgreich.";
        $failureMessage = $message ?? "Ein Fehler ist aufgetreten.";

        // Set success or failure message depending on success
        $message = [
            "title" => $success ? "Erfolg" : "Fehler",
            "text" => $success ? $successMessage : $failureMessage
        ];

        return response()->json([
            "success" => $success,
            "data" => $data,
            "message" => $message
        ]);
    }
}
