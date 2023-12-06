<?php


namespace App\Api\Connections;


use App\Exceptions\ExtendedException;

interface ConnectionInterface
{
    /**
     * ConnectionInterface constructor
     * @param array $config
     * @param array $requiredConfigAttributeKeys
     * @param object $instance
     * @param array $validInstances
     */
    public function __construct(array $config = [], array $requiredConfigAttributeKeys = [], object $instance = null,
                                array $validInstances = []);

    /**
     * Establish a connection to the configured server with the configured protocol
     * @throws ExtendedException
     */
    public function establish() : void;

    /**
     * Terminate the active connection to the configured server with the configured protocol
     * @throws ExtendedException
     */
    public function terminate() : void;

    /**
     * Get the connection instance
     * @return Connection
     */
    public function get() : object;
}
