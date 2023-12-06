<?php


namespace App\Processors\Readers;


use App\Api\Connections\Connection;
use App\Collections\AmavisServerCollection;
use App\Collections\ConnectionCollection;
use App\Components\AmavisServer;
use App\Processors\EmailProcessor;
use App\Exceptions\ExtendedException;
use App\Utils\ArrayUtil;

abstract class Reader extends EmailProcessor implements ReaderInterface
{
    /**
     * A connection or connection collection of the provided type
     * @var null|Connection|ConnectionCollection
     */
    protected $connection = null;

    /**
     * Amavis servers
     * @var null|AmavisServerCollection
     */
    protected $amavisServerCollection = null;

    /**
     * Reader constructor
     * @param object $connection
     * @param AmavisServerCollection $amavisServerCollection
     * @throws ExtendedException
     */
    public function __construct(object $connection, AmavisServerCollection $amavisServerCollection) {

        // Set connection
        $this->setConnection($connection);

        // Set Amavis servers
        $this->setAmavisServerCollection($amavisServerCollection);
    }

    /**
     * Set the connection
     * @param object $connection
     * @throws ExtendedException
     */
    private function setConnection(object $connection) : void {

        // Specify valid connection types
        $validConnectionTypes = [
            Connection::class,
            ConnectionCollection::class
        ];

        // Validate connection
        $matchingValidTypeFound = false;
        foreach ($validConnectionTypes as $validConnectionType) {
            if ($connection instanceof $validConnectionType)
                $matchingValidTypeFound = true;
        }
        if (!$matchingValidTypeFound) {
            $providedType = get_class($this->connection);
            $validTypesString = ArrayUtil::toString($validConnectionTypes);
            throw new ExtendedException("Passed parameter '\$connection must be one of following types: " .
                "'$validTypesString', '$providedType' provided.");
        }


        // Set connection
        $this->connection = $connection;
    }

    /**
     * Set Amavis servers
     * @param AmavisServerCollection $amavisServerCollection
     * @throws ExtendedException
     */
    private function setAmavisServerCollection(AmavisServerCollection $amavisServerCollection) : void {
        foreach ($amavisServerCollection as $amavisServer) {

            // If any array element not type of "App\Components\AmavisServer", throw exception
            if (!$amavisServer instanceof AmavisServer) {
                $passedType = gettype($amavisServer);
                $requiredType = AmavisServer::class;
                throw new ExtendedException("Each item of passed collection '\$amavisServerCollection' must be of type " .
                    "'$requiredType', '$passedType' found.");
            }
        }

        $this->amavisServerCollection = $amavisServerCollection;
    }
}
