<?php


namespace App\Overrides\Webklex;


use App\Exceptions\ExtendedException;
use Webklex\IMAP\Client;
use Webklex\IMAP\Exceptions\ConnectionFailedException;

class Folder extends \Webklex\IMAP\Folder
{
    /**
     * Amavis server name from where the "email was isolated message" originally was sent
     * @var string
     */
    private $amavisServerName = "";

    /**
     * Class which represents the desired target format of the message
     * @var null|string
     */
    private $targetEmailType = "";

    /**
     * Specifies if specific attributes of the override should be enabled
     * @var bool
     */
    private $specialAttributesEnabled = false;

    /**
     * Folder constructor
     * @param Client $client
     * @param object $structure
     */
    public function __construct(Client $client, object $structure) {

        // Parent construction
        parent::__construct($client, $structure);
    }

    /**
     * Get a new search query instance
     * @param string $charset
     * @return WhereQuery
     * @throws ConnectionFailedException
     * @throws ExtendedException
     */
    public function query($charset = 'UTF-8') {

        // If special attributes required
        if ($this->specialAttributesEnabled) {

            // If one of the specified attributes not set, throw exception
            $attributes = [
                "amavisServerName",
                "targetEmailType"
            ];
            foreach ($attributes as $attribute) {
                if (strlen($this->{$attribute}) <= 0)
                    throw new ExtendedException("Attribute '$attribute' not set.");
            }
        }

        $this->getClient()->checkConnection();
        $this->getClient()->openFolder($this->path);

        return new WhereQuery($this->getClient(), $this->amavisServerName, $this->targetEmailType, 'UTF-8');
    }

    /**
     * Enable the consideration of special attributes
     */
    public function enableSpecialAttributes() : void {
        $this->setSpecialAttributesEnabled(true);
    }

    /**
     * Disable the consideration of special attributes
     */
    public function disableSpecialAttributes() : void {
        $this->setSpecialAttributesEnabled(false);
    }

    /**
     * Set special attributes enabled
     * @param bool $enable
     */
    private function setSpecialAttributesEnabled(bool $enable) : void {
        $this->specialAttributesEnabled = $enable;
    }

    /**
     * Set the Amavis server name where messages will be queried from
     * Is a special attribute which in some cases isn't needed
     * @param string $amavisServerName
     */
    public function setAmavisServerName(string $amavisServerName) : void {
        if ($this->specialAttributesEnabled)
            $this->amavisServerName = $amavisServerName;
    }

    /**
     * Set the class which represents the desired target format of the message
     * Is a special attribute which in some cases isn't needed
     * @param string $targetEmailType
     */
    public function setTargetEmailType(string $targetEmailType) : void {
        if ($this->specialAttributesEnabled)
            $this->targetEmailType = $targetEmailType;
    }
}
