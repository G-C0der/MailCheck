<?php


namespace App\Processors\Handlers;


use App\Api\Connections\ImapConnection;
use App\Exceptions\ExtendedException;
use App\Processors\Components\MailboxConfig;
use App\Processors\EmailProcessor;
use Webklex\IMAP\Exceptions\ConnectionFailedException;
use Webklex\IMAP\Exceptions\GetMessagesFailedException;
use Webklex\IMAP\Exceptions\InvalidWhereQueryCriteriaException;

class RequestEmailHandler extends EmailProcessor
{
    /**
     * IMAP Client
     * @var null|ImapConnection
     */
    private $imapConnection = null;

    /**
     * The mailbox config of the requests email inbox and the requests email archive mailbox
     * @var null|MailboxConfig
     */
    private $mailboxConfig = null;

    /**
     * RequestEmailHandler constructor
     * @param ImapConnection $imapConnection
     */
    public function __construct(ImapConnection $imapConnection) {

        // Set IMAP connection
        $this->imapConnection = $imapConnection;
    }

    /**
     * Move the request emails which were successfully written into the database into the archive folder
     * If no archive Folder exists, create it
     * @param array $amavisIdentifiers
     * @throws ExtendedException
     */
    public function archiveRequestEmails(array $amavisIdentifiers) : void {

        // Validate mailboxes
        if (!isset($this->mailboxConfig))
            throw new ExtendedException("Property '\$this->mailboxConfig' cannot be null.");

        // Set inbox
        $requestsInboxName = $this->mailboxConfig->getSourceFolderName();
        $requestsInbox = $this->imapConnection->get()->getFolder($requestsInboxName);

        // Set archive mailbox
        $requestsArchiveName = $this->mailboxConfig->getTargetFolderName(true);

        try {

            // Get request emails which were saved into the database
            // We have to do this with an ugly foreach, because most IMAP clients don't support extended querying, which
            // in our case would be an "$query->where()" for the first Amavis identifier in "$amavisIdentifiers", followed
            // by an "->orWhere" for each remaining Amaivs identifier
            $requestEmails = [];
            foreach ($amavisIdentifiers as $amavisIdentifier) {
                $savedRequestEmail = $requestsInbox->query()

                    // Query the email which match the provided Amavis identifier
                    ->whereText($amavisIdentifier)
                    ->setFetchFlags(false)
                    ->setFetchBody(true)
                    ->setFetchAttachment(false)
                    ->get()
                    ->first();

                // If email not found, throw exception
                if (!isset($savedRequestEmail))
                    throw new ExtendedException("Request email with the Amavis identifier " .
                        "'$amavisIdentifier' not found in mailbox folder '$requestsInboxName'.");

                // Save the queried email
                $requestEmails[] = $savedRequestEmail;
            }

            // Move the saved emails to the archive folder
            foreach ($requestEmails as $savedRequestEmail)
                $savedRequestEmail->moveToFolder($requestsArchiveName);
        } catch (GetMessagesFailedException $exception) {
            throw new ExtendedException("IMAP message retrieval failed: " . $exception->getMessage());
        } catch (ConnectionFailedException $exception) {
            throw new ExtendedException("IMAP connection aborted: " . $exception->getMessage());
        } catch (InvalidWhereQueryCriteriaException $exception) {
            throw new ExtendedException("Invalid IMAP where query criteria: " . $exception->getMessage());
        }
    }

    /**
     * Set mailbox config
     * @param MailboxConfig $mailboxConfig
     */
    public function setMailboxConfig(MailboxConfig $mailboxConfig) : void {
        $this->mailboxConfig = $mailboxConfig;
    }
}
