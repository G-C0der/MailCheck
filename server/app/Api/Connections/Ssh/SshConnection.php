<?php


namespace App\Api\Connections\Ssh;


use App\Api\Connections\Connection;
use App\Exceptions\ExtendedException;
use phpseclib\Net\SSH2;

class SshConnection extends Connection
{
    /**
     * Valid instance
     */
    private const VALID_INSTANCE = SSH2::class;

    /**
     * SftpConnection constructor
     * @param array $config
     * @param array $requiredConfigAttributeKeys
     * @param object|null $instance
     * @param array $validInstances
     * @throws ExtendedException
     */
    public function __construct(array $config = [], array $requiredConfigAttributeKeys = [ "host", "port", "username",
        "password" ], object $instance = null, $validInstances = []) {

        // Parent construction
        $instance = $instance ?? new SSH2($config["host"], $config["port"]);
        $validInstances[] = self::VALID_INSTANCE;
        parent::__construct($config, $requiredConfigAttributeKeys, $instance, $validInstances);
    }

    /**
     * Establish the SFTP connection
     * @throws ExtendedException
     */
    public function establish() : void {

        // Authenticate
        $isConnected = $this->instance->login($this->config["username"], $this->config["password"]);
        if (!$isConnected) {
            $host = $this->config["host"];
            $port = $this->config["port"];
            $username = $this->config["username"];
            throw new ExtendedException("SSH authentication on host '$host:$port' with user '$username' " .
                "failed.");
        }
    }

    /**
     * Terminate the SFTP connection
     */
    public function terminate() : void {
        $this->instance->disconnect();
    }
}
