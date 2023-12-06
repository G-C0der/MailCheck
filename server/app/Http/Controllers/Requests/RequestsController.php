<?php

namespace App\Http\Controllers\Requests;

use App\Exceptions\ExtendedException;
use App\Http\Controllers\Controller;
use App\Models\Requests\RequestsModel;
use App\Processors\Handlers\QuarantineEmailHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestsController extends Controller
{
    /**
     * RequestsController constructor
     */
    public function __construct() {
        $this->middleware("auth");
    }

    /**
     * Get all request emails
     * @return JsonResponse
     * @throws ExtendedException
     */
    public function getAllRequests() : JsonResponse {
        $modelRequestEmail = new RequestsModel();
        $allRequestEmails = $modelRequestEmail->queryAll([
            "status" => "asc",
            "timestamp" => "desc",
            "sender_email" => "desc"
        ]);
        return $this->jsonResponse($allRequestEmails, "Keine Freigabeanfragen gefunden.");
    }

    /**
     * Forward quarantine email to the logged in user
     * @param Request $request
     * @return JsonResponse
     * @throws ExtendedException
     */
    public function forwardRequest(Request $request) : JsonResponse {

        // Get parameters
        $amavisIdentifier = $request->get("amavisIdentifier");

        // Get the request via Amavis identifier
        $requestsModel = new RequestsModel();
        $releaseRequest = $requestsModel->where("amavis_identifier", $amavisIdentifier)->first();

        // If request doesnt exist, throw exception
        if (!$releaseRequest)
            throw new ExtendedException("Request with Amavis identifier '$amavisIdentifier' not found in " .
                "database.");

        // Handle
        $quarantineEmailHandler = new QuarantineEmailHandler();
        $requestForwardingResult = $quarantineEmailHandler->forwardToLoggedInUser($releaseRequest);

        // Return if everything went as expected
        return $this->jsonResponse($requestForwardingResult["value"], $requestForwardingResult["message"]);
    }

    /**
     * Handle the request with the given id, based on the provided "$release" boolean
     * @param Request $request
     * @return JsonResponse
     * @throws ExtendedException
     */
    public function handleRequest(Request $request) : JsonResponse {

        // Get parameters
        $amavisIdentifier = $request->get("amavisIdentifier");
        $release = filter_var($request->get("release"), FILTER_VALIDATE_BOOLEAN);
        $reason = trim($request->get("reason"));

        // Get the request via Amavis identifier
        $requestsModel = new RequestsModel();
        $releaseRequest = $requestsModel->where("amavis_identifier", $amavisIdentifier)->first();

        // If request doesnt exist, throw exception
        if (!$releaseRequest)
            throw new ExtendedException("Request with Amavis identifier '$amavisIdentifier' not found in " .
                "database.");

        // Handle
        $quarantineEmailHandler = new QuarantineEmailHandler();
        $requestHandlingResult = $quarantineEmailHandler->handle($releaseRequest, $release, $reason);

        // Change the release request state from "pending" to "done", if the handling result is positive
        $requestStateChangingResult = false;
        if ($requestHandlingResult["value"])
            $requestStateChangingResult = $requestsModel->setRequestDone($amavisIdentifier);

        // Return if everything went as planned
        return $this->jsonResponse($requestHandlingResult["value"] && $requestStateChangingResult,
            $requestHandlingResult["message"]);
    }
}
