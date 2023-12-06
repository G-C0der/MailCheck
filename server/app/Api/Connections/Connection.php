<?php


namespace App\Api\Connections;


use App\Exceptions\ExtendedException;
use App\Utils\ArrayUtil;

abstract class Connection implements ConnectionInterface
{
    /**
     * The connection instance
     * @var null|object
     */
    protected $instance = null;

    /**
     * The config
     * @var array
     */
    protected $config = [];

    /**
     * The keys of the required config attributes
     * @var array
     */
    private $requiredConfigAttributeKeys = [];

    /**
     * Connection constructor
     * @param array $config
     * @param array $requiredConfigAttributeKeys
     * @param object|null $instance
     * @param array $validInstances
     * @throws ExtendedException
     */
    public function __construct(array $config = [], array $requiredConfigAttributeKeys = [ "host", "port", "username",
        "password" ], object $instance = null, array $validInstances = []) {

        // Validate instance
        $this->validateInstance($instance, $validInstances);

        // Set the instance
        $this->instance = $instance;

        // Set required config attribute keys
        $this->requiredConfigAttributeKeys = $requiredConfigAttributeKeys;

        // Set the config
        $this->setConfig($config);
    }

    /**
     * Validate the instance
     * @param object|null $instance
     * @param array $validInstances
     * @throws ExtendedException
     */
    private final function validateInstance(?object $instance, array $validInstances) : void {
        foreach ($validInstances as $validInstance) {
            if ($instance instanceof $validInstance)
                return;
        }
        $instanceClass = $instance ? get_class($instance) : $instance;
        $validInstancesString = ArrayUtil::toString($validInstances);
        throw new ExtendedException("Instance '$instanceClass' not found in valid instances " .
            "'$validInstancesString'");
    }

    /**
     * Set the config
     * @param array $config
     * @throws ExtendedException
     */
    public final function setConfig(array $config) : void {

        // Validate the config
        ArrayUtil::validateAttributes($config, $this->requiredConfigAttributeKeys);

        // Set the config
        $this->config = $config;
    }

    /**
     * Get the IMAP connection instance
     */
    public final function get() : object {
        return $this->instance;
    }
}
