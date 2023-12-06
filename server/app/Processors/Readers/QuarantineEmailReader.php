<?php


namespace App\Processors\Readers;


use App\Collections\AmavisServerCollection;
use App\Emails\QuarantineEmail;
use App\Exceptions\ExtendedException;

class QuarantineEmailReader extends Reader
{
    /**
     * Amavis identifier of the quarantine email which should be read
     * @var string
     */
    private $currentAmavisIdentifier = "";

    /**
     * Amavis server where the current Amavis identifier locates an isolated email
     * @var string
     */
    private $currentAmavisServerName = "";

    /**
     * List of Amavis identifiers, which already have been used to fetch an isolated email
     * @var array
     */
    private $usedAmavisIdentifiers = [];

    /**
     * The target email type of each fetched email
     * @var string
     */
    private const TARGET_EMAIL_TYPE = QuarantineEmail::class;

    /**
     * QuarantineEmailReader constructor
     * @param object $connection
     * @param AmavisServerCollection $amavisServerCollection
     * @throws ExtendedException
     */
    public function __construct(object $connection, AmavisServerCollection $amavisServerCollection) {

        // Parent construction
        parent::__construct($connection, $amavisServerCollection);
    }

    /**
     * Read quarantine email data from specified Amavis server
     * @return array
     * @throws ExtendedException
     */
    public function read() : array {

        // Validate attributes
        $this->validateAttributes();

        // Fetch the isolated email data via SFTP on the Amavis server
        $sftpConnection = $this->connection->get($this->currentAmavisServerName);
        $sftpInstance = $sftpConnection->get();
        $quarantineEmailPath = $this->getCurrentIsolationPath() . $this->currentAmavisIdentifier;
        $quarantineEmailData = $sftpInstance->get($quarantineEmailPath);

        // Mark the current Amavis identifier as used
        $this->usedAmavisIdentifiers[] = $this->currentAmavisIdentifier;

        // If no quarantine email data fetched, throw exception
        if (!$quarantineEmailData)
            throw new ExtendedException("Quarantine email '" . $this->getCurrentIsolationPath() .
                "$this->currentAmavisIdentifier' not found on Amavis server '$this->currentAmavisServerName'.");

        // Provide the Amavis identifier for saving it into an "App\Emails\QuarantineEmail" instance
        $quarantineEmailData = [
            "email" => $quarantineEmailData,
            "amavisIdentifier" => $this->currentAmavisIdentifier,
            "amavisServerName" => $this->currentAmavisServerName
        ];

        // Return the quarantine email data with the target email format type
        return [
            "quarantineEmailData" => $quarantineEmailData,
            "targetEmailType" => self::TARGET_EMAIL_TYPE
        ];
    }

    /**
     * Validate needed attributes to read
     * @throws ExtendedException
     */
    private function validateAttributes() : void {

        // Attributes to validate
        $attributes = [
            "currentAmavisIdentifier",
            "currentAmavisServerName"
        ];

        // If one of the specified attributes not set, throw Exception
        foreach ($attributes as $attribute) {
            $notSet = is_array($this->{$attribute}) ? sizeof($this->{$attribute}) <= 0 :
                strlen($this->{$attribute}) <= 0;
            if ($notSet)
                throw new ExtendedException("Attribute '\$this->$attribute' is empty.");
        }
    }

    /**
     * Set Amavis identifier
     * @param string $amavisIdentifier
     * @throws ExtendedException
     */
    public function setAmavisIdentifier(string $amavisIdentifier) : void {

        // Validate Amavis identifier
        self::validateAmavisIdentifier($amavisIdentifier);

        // If isolated email content of passed Amavis identifier already fetched, throw exception
        if (in_array($amavisIdentifier, $this->usedAmavisIdentifiers))
            throw new ExtendedException("Amavis identifier '$amavisIdentifier' content already fetched.");

        // Set
        $this->currentAmavisIdentifier = $amavisIdentifier;
    }

    /**
     * Set Amavis server name
     * @param string $amavisServerName
     * @throws ExtendedException
     */
    public function setAmavisServerName(string $amavisServerName) : void {

        // If $amavisServerName is empty, throw exception
        if (strlen($amavisServerName) <= 0)
            throw new ExtendedException("Passed parameter '\$amavisServerName' cannot contain an empty " .
                "string.");

        // Set
        $this->currentAmavisServerName = $amavisServerName;
    }

    /**
     * Get the isolation path of the Amavis server of the quarantine email which currently should be read
     * @return string
     */
    private function getCurrentIsolationPath() : string {
        return $this->amavisServerCollection->get($this->currentAmavisServerName)->getIsolationPath();
    }
}
