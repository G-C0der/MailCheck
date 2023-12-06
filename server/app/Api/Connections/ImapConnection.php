<?php


namespace App\Api\Connections;


use App\Exceptions\ExtendedException;
use App\Overrides\Webklex\Client;
use Webklex\IMAP\Exceptions\ConnectionFailedException;
use Webklex\IMAP\Exceptions\MaskNotFoundException;

class ImapConnection extends Connection
{
    /**
     * Valid instance
     */
    private const VALID_INSTANCE = Client::class;

    /**
     * ImapConnection constructor
     * @param array $config
     * @param array $requiredConfigAttributeKeys
     * @param object|null $instance
     * @param array $validInstances
     * @throws ExtendedException
     */
    public function __construct(array $config = [], array $requiredConfigAttributeKeys = [ "host", "port", "encryption",
                                "boolean" => "validate_cert", "username", "password", "protocol" ],
                                object $instance = null, $validInstances = []) {

        // Parent construction
        try {
            $instance = $instance ?? new Client($config);
            $validInstances[] = self::VALID_INSTANCE;
            parent::__construct($config, $requiredConfigAttributeKeys, $instance, $validInstances);
        } catch (MaskNotFoundException $exception) {
            throw new ExtendedException("IMAP config mask not found: " . $exception->getMessage());
        }
    }

    /**
     * Establish the IMAP connection
     * @throws ExtendedException
     */
    public function establish() : void {
        $host = $this->config['host'];
        $exceptionMessage = "Could not connect to IMAP host '$host'";

        // Try to connect, if not possible throw exception
        try {
            $this->instance->connect();
        } catch (ConnectionFailedException $exception) {
            throw new ExtendedException("$exceptionMessage: " . $exception->getMessage());
        }

        // If not connected throw exception
        if (!$this->instance->isConnected())
            throw new ExtendedException($exceptionMessage);
    }

    /**
     * Terminate the IMAP connection
     * @throws ExtendedException
     */
    public function terminate() : void {
        $this->instance->disconnect();

        // If connected throw exception
        if ($this->instance->isConnected()) {
            $host = $this->config['host'];
            throw new ExtendedException("Could not disconnect from IMAP host '$host'");
        }
    }
}
