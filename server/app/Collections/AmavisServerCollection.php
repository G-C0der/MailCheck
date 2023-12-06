<?php


namespace App\Collections;


use App\Components\AmavisServer;
use App\Exceptions\ExtendedException;

class AmavisServerCollection extends Collection
{
    /**
     * The only valid types in this collection
     */
    private const VALID_TYPES = [
        AmavisServer::class
    ];

    /**
     * AmavisServerCollection constructor
     * @param array $amavisServers
     * @throws ExtendedException
     */
    public function __construct(array $amavisServers = []) {

        // Get Amavis server suffixes from env file
        // A list of the different suffixes (which determine the Amaivs server) of the different Amavis server constants
        // in the env file
        $amavisServerEnvConstantsSuffixes = env("AMAVIS_SERVERS");
        $amavisServerEnvConstantsSuffixes = explode(",", $amavisServerEnvConstantsSuffixes);

        // Set Amavis servers information
        foreach ($amavisServerEnvConstantsSuffixes as $suffix) {
            $amavisServer = new AmavisServer($suffix);
            $amavisServers[$amavisServer->getName()] = $amavisServer;
        }

        // Parent construction
        parent::__construct($amavisServers, self::VALID_TYPES);
    }
}
