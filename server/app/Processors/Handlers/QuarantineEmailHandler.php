<?php


namespace App\Processors\Handlers;


use App\Api\Connections\Ssh\SshConnection;
use App\Collections\AmavisServerCollection;
use App\Exceptions\ExtendedException;
use App\Models\Requests\RequestsModel;
use App\Processors\EmailProcessor;
use Illuminate\Support\Facades\Auth;
use phpseclib\Net\SSH2;

class QuarantineEmailHandler extends EmailProcessor
{
    /**
     * Amavis release command
     * Example: "amavisd-release $amavisIdentifier email@example.com"
     * If email argument provided, the quarantine email will be forwarded to the email, else it will be released
     */
    private const COMMAND_RELEASE = "amavisd-release";

    /**
     * Regex to check if the Amavis server process succeeded
     */
    private const PROCESS_SUCCESS_REGEX = ".*Ok.*";

    /**
     * Forward quarantine email to the logged in user
     * For attachment scanning purposes
     * @param RequestsModel $releaseRequest
     * @return array
     * @throws ExtendedException
     */
    public function forwardToLoggedInUser(RequestsModel $releaseRequest) : array {

        // If no user logged in, throw exception
        if (!Auth::user())
            throw new ExtendedException("Logged in user could not be retrieved.");

        // Connect to Amavis server via SSH
        $sshConnection = $this->establishAmavisServerSshConnection($releaseRequest);

        // Forward email
        $loggedInUserEmail = Auth::user()->email;
        $forwardQuarantineEmailCommand = self::COMMAND_RELEASE . " $releaseRequest->amavis_identifier " .
            $loggedInUserEmail;
        $commandOutput = $sshConnection->get()->exec($forwardQuarantineEmailCommand);

        // Validate result
        return [
            "value" => $this->isCommandResultPositive($commandOutput),
            "message" => $commandOutput
        ];
    }

    /**
     * Handle the release request based on the given parameters
     * @param RequestsModel $releaseRequest
     * @param bool $release
     * @param string $reason
     * @return array
     * @throws ExtendedException
     */
    public function handle(RequestsModel $releaseRequest, bool $release, string $reason) : array {
        if ($release) {

            // Release quarantine email
            $commandOutput = $this->releaseQuarantineEmail($releaseRequest);
            $releaseResult = $this->isCommandResultPositive($commandOutput);

            // If release result positive, notify requester with a positive email
            $notifyResult = false;
            if ($releaseResult)
                $notifyResult = $this->notifyRequester($releaseRequest, $release);

            return [
                "value" => $releaseResult && $notifyResult,
                "message" => $commandOutput
            ];
        }

        // Notify requester with a negative email
        return [
            "value" => $this->notifyRequester($releaseRequest, $release, $reason),
            "message" => null
        ];
    }

    /**
     * Release the quarantine email from the Amavis server
     * @param RequestsModel $releaseRequest
     * @return string
     * @throws ExtendedException
     */
    private function releaseQuarantineEmail(RequestsModel $releaseRequest) : string {

        // Connect to the Amavis server via SSH
        $sshConnection = $this->establishAmavisServerSshConnection($releaseRequest);

        // Release the quarantine email
        $releaseQuarantineEmailCommand = self::COMMAND_RELEASE . " $releaseRequest->amavis_identifier";
        return $sshConnection->get()->exec($releaseQuarantineEmailCommand);
    }

    /**
     * Notify the release requester via email that the quarantine email was released / stashed
     * Will be implemented in a later version
     * @param RequestsModel $releaseRequest
     * @param bool $release
     * @param string $reason
     * @return bool
     */
    private function notifyRequester(RequestsModel $releaseRequest, bool $release, string $reason = "") : bool {
        return true;
    }

    /**
     * Connect to Amavis server where the quarantine email of the given request is located on via SSH
     * @param RequestsModel $releaseRequest
     * @return SshConnection
     * @throws ExtendedException
     */
    private function establishAmavisServerSshConnection(RequestsModel $releaseRequest) : SshConnection {
        $amavisServerName = $releaseRequest->quarantineEmail->amavisServer->name;
        $amavisServerCollection = new AmavisServerCollection();
        $amavisServer = $amavisServerCollection->get($amavisServerName);
        $sshConnection = new SshConnection([
            "host" => $amavisServer->getIp(),
            "port" => $amavisServer->getSshPort(),
            "username" => $amavisServer->getSshUsername(),
            "password" => $amavisServer->getSshPassword()
        ]);
        $sshConnection->establish();
        return $sshConnection;
    }

    /**
     * Check if the result from the command executed on the Amavis server is positive
     * @param string $result
     * @return bool
     * @throws ExtendedException
     */
    private function isCommandResultPositive(string $result) : bool {
        $regex = self::buildRegex([
            self::PROCESS_SUCCESS_REGEX
        ]);
        return preg_match($regex, $result) === 1;
    }
}
