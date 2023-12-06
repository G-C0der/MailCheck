<?php


namespace App\Processors\Readers;


use App\Collections\AmavisServerCollection;
use App\Components\AmavisServer;
use App\Emails\RequestEmail;
use App\Exceptions\ExtendedException;
use App\Processors\Components\MailboxConfig;
use Webklex\IMAP\Exceptions\ConnectionFailedException;
use Webklex\IMAP\Exceptions\GetMessagesFailedException;
use Webklex\IMAP\Exceptions\InvalidWhereQueryCriteriaException;
use Webklex\IMAP\Support\MessageCollection;

class RequestEmailReader extends Reader
{
    /**
     * The mailbox config
     * @var null|MailboxConfig
     */
    private $mailboxConfig = null;

    /**
     * The target email type of each fetched email
     * @var string
     */
    private const TARGET_EMAIL_TYPE = RequestEmail::class;

    /**
     * RequestInboxReader constructor
     * @param object $connection
     * @param AmavisServerCollection $amavisServerCollection
     * @throws ExtendedException
     */
    public function __construct(object $connection, AmavisServerCollection $amavisServerCollection) {

        // Parent construction
        parent::__construct($connection, $amavisServerCollection);
    }

    /**
     * Get all unread request emails from the predefined Amavis servers
     * @return array
     * @throws ExtendedException
     */
    public function read() : array {

        // If not connected, throw exception
        if (!$this->connection->get()->isConnected())
            throw new ExtendedException("No IMAP connection active.");

        // If no Amavis servers specified, throw exception
        if (sizeof($this->amavisServerCollection->all()) <= 0)
            throw new ExtendedException("No Amavis servers specified in attribute '\$this->amavisServers''.");

        $result = [];

        // Foreach Amavis server
        foreach ($this->amavisServerCollection->all() as $amavisServer) {

            // Read emails from the current Amavis server
            $serverResult = $this->getNewRequestEmails($amavisServer);

            // Save read emails of the current Amavis server into results
            $result = array_merge($result, $serverResult->all());
        }

        return $result;
    }

    /**
     * Get unread request emails of the specified Amavis server
     * @param AmavisServer $amavisServer
     * @return MessageCollection
     * @throws ExtendedException
     */
    private function getNewRequestEmails(AmavisServer $amavisServer) : MessageCollection {

        // Validate mailbox
        if (!isset($this->mailboxConfig))
            throw new ExtendedException("Property '\$this->mailboxConfig' cannot be null.");

        try {

            // Set inbox
            $requestsInboxName = $this->mailboxConfig->getSourceFolderName();
            $requestsInbox = $this->connection->get()->getFolder($requestsInboxName);

            // Enable the consideration of Amavis attributes on "\Webklex\IMAP\Folder" override "$requestsInbox"
            $requestsInbox->enableSpecialAttributes();

            // Set Amavis attributes on "\Webklex\IMAP\Folder" override "$requestsInbox"
            $amavisServerName = $amavisServer->getName();
            $requestsInbox->setAmavisServerName($amavisServerName);
            $requestsInbox->setTargetEmailType(self::TARGET_EMAIL_TYPE);

            // Set the representation of the Amavis server in a request email
            $amavisServerIdentifier = $amavisServer->getAmavisServerRepresentation();

            // Retrieve new request emails
            return $requestsInbox->query()

                // Query mails which contain the specified representation of the specified Amavis server in their
                // message body
                ->whereText($amavisServerIdentifier)

                // Query mails which contain the static Amavis identifier part "/banned" in their message body
                ->whereText(self::NEEDLE_AMAVIS_IDENTIFIER)
                ->setFetchFlags(false)
                ->setFetchBody(true)
                ->setFetchAttachment(false)
                ->get();
        } catch (GetMessagesFailedException $exception) {
            throw new ExtendedException("IMAP message retrieval failed: " . $exception->getMessage());
        } catch (ConnectionFailedException $exception) {
            throw new ExtendedException("IMAP connection aborted: " . $exception->getMessage());
        } catch (InvalidWhereQueryCriteriaException $exception) {
            throw new ExtendedException("Invalid IMAP where query criteria: " . $exception->getMessage());
        }
    }

    /**
     * Set the mailbox config
     * @param MailboxConfig $mailboxConfig
     */
    public function setMailboxConfig(MailboxConfig $mailboxConfig) : void {
        $this->mailboxConfig = $mailboxConfig;
    }
}
