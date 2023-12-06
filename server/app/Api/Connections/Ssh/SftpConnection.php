<?php


namespace App\Api\Connections\Ssh;


use App\Exceptions\ExtendedException;
use phpseclib\Net\SFTP;

class SftpConnection extends SshConnection
{
    /**
     * Valid instance
     */
    private const VALID_INSTANCE = SFTP::class;

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
        $instance = $instance ?? new SFTP($config["host"], $config["port"]);
        $validInstances[] = self::VALID_INSTANCE;
        parent::__construct($config, $requiredConfigAttributeKeys, $instance, $validInstances);
    }
}
