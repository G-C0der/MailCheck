<?php


namespace App\Collections;


use App\Collections\Components\EmailPair;
use App\Emails\QuarantineEmail;
use App\Factories\EmailFactory;
use App\Overrides\Webklex\Message;
use App\Processors\Readers\QuarantineEmailReader;
use App\Exceptions\ExtendedException;

class EmailCollection extends Collection
{
    /**
     * The email factory used for creating new "App\Emails\Email" objects
     * @var null|EmailFactory
     */
    private $emailFactory = null;

    /**
     * Reads isolated email data from the Amavis server quarantine
     * @var null|QuarantineEmailReader
     */
    private $quarantineEmailReader = null;

    /**
     * The only valid types in this collection
     */
    private const VALID_TYPES = [
        Message::class
    ];

    /**
     * EmailCollection constructor
     * @param QuarantineEmailReader $quarantineEmailReader
     * @param array $requestMessages
     * @throws ExtendedException
     */
    public function __construct(QuarantineEmailReader $quarantineEmailReader, array $requestMessages = []) {

        // Parent construction - no items added because all emails get added with the "add()" method in the constructor,
        // where the quarantine retrieval process takes place
        parent::__construct([], self::VALID_TYPES);

        // Adding the "$requestMessages"
        foreach ($requestMessages as $requestMessage)
            $this->add($requestMessage);

        // Set the email factory
        $this->emailFactory = new EmailFactory();

        // Set the Amavis quarantine reader
        $this->quarantineEmailReader = $quarantineEmailReader;
    }

    /**
     * Add an item to the collection.
     * @param Message $requestMessage
     * @param string $key
     * @return $this
     * @throws ExtendedException
     */
    public function add($requestMessage, string $key = null) : parent {

        // Validate "$requestMessage"
        $this->validateItems($requestMessage);

        // Create request email object, which only contains relevant data, from the message
        $requestEmail = $this->emailFactory->create($requestMessage);

        // Create quarantine email object
        $amavisIdentifier = $requestEmail->getAmavisIdentifier();
        $amavisServerName = $requestEmail->getAmavisServerName();
        $quarantineEmail = $this->retrieveQuarantineEmail($amavisIdentifier, $amavisServerName);

        // Save the request-quarantine email pair into the collection
        $this->items[] = new EmailPair($requestEmail, $quarantineEmail);
        return $this;
    }

    /**
     * Retrieve quarantine email from Amavis identifier
     * @param string $amavisIdentifier
     * @param string $amavisServerName
     * @return QuarantineEmail
     * @throws ExtendedException
     */
    private function retrieveQuarantineEmail(string $amavisIdentifier, string $amavisServerName) : QuarantineEmail {

        // Read content of the isolated email on the Amavis server
        $this->quarantineEmailReader->setAmavisIdentifier($amavisIdentifier);
        $this->quarantineEmailReader->setAmavisServerName($amavisServerName);
        $quarantineEmailContent = $this->quarantineEmailReader->read();

        // Create a "App\Emails\QuarantineEmail" object, which only contains relevant data
        return $this->emailFactory->create($quarantineEmailContent);
    }
}
