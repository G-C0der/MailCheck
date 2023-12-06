<?php

namespace App\Console\Commands;

use App\Api\Connections\ImapConnection;
use App\Api\Connections\Ssh\SftpConnection;
use App\Collections\AmavisServerCollection;
use App\Collections\ConnectionCollection;
use App\Components\AmavisServer;
use App\Collections\EmailCollection;
use App\Models\Requests\AmavisServerModel;
use App\Overrides\Webklex\Message;
use App\Processors\Components\MailboxConfig;
use App\Processors\Handlers\RequestEmailHandler;
use App\Processors\Readers\QuarantineEmailReader;
use App\Processors\Readers\RequestEmailReader;
use App\Exceptions\ExtendedException;
use App\Models\Requests\RequestsModel;
use Illuminate\Console\Command;
use ReflectionException;

class FetchRequestsCommand extends Command
{
    /**
     * The name and signature of the console command
     * @var string
     */
    protected $signature = 'requests:fetch';

    /**
     * The console command description
     * @var string
     */
    protected $description = 'Fetching release request email from release request mailbox and associated quarantine
    email information from Amavis server. Fetched information gets written to database.';

    /**
     * Relevant Amavis servers where the quarantine emails are isolated
     * @var null|AmavisServerCollection
     */
    private $amavisServerCollection = null;

    /**
     * The config of the request email inbox and the request email archive mailbox
     * @var null|MailboxConfig
     */
    private $requestMailboxConfig = null;

    /**
     * The IMAP connection for accessing the release request mailbox
     * @var null|ImapConnection
     */
    private $imapConnection = null;

    /**
     * The SFTP connections for accessing the relevant Amaivs servers where the quarantine emails are stored
     * @var null|ConnectionCollection
     */
    private $sftpConnectionCollection = null;

    /**
     * Unparsed request email data
     * @var Message[]
     */
    private $requestMessages = [];

    /**
     * Email collection
     * Contains request-quarantine email pairs
     * @var null|EmailCollection
     */
    private $emailCollection = null;

    /**
     * Results from saving the data into the database
     * Contains the Amavis identifier of each request-quarantine email pair, which was successfully written into the
     * database
     * @var string[]
     */
    private $databaseSavingResults = [];

    /**
     * Create a new command instance
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command
     * @throws ExtendedException
     * @throws ReflectionException
     */
    public function handle() {

        // Set relevant Amavis servers where the quarantine emails are isolated
        $this->setAmavisServerCollection();

        // Save Amavis server names to database (if not already saved) because we need to know where to release the
        // email later in the release process
        $this->saveAmavisServerNamesToDatabase();

        // Set release request mailbox config
        $this->setRequestMailboxConfig();

        // Fetch unparsed request email data
        $this->fetchRequestEmails();

        // Fill the email collection
        $this->fillEmailCollection();

        // Pass the collection to the models, where all fetched mail data gets written into the database
        $this->saveToDatabase();

        // Move saved emails into the archive folder
        $this->moveRequestEmails();

        // Success message
        $this->info("Process successfully finished.");
    }

    /**
     * Set relevant Amavis servers where the quarantine emails are isolated
     * @throws ExtendedException
     */
    private function setAmavisServerCollection() : void {
        $this->amavisServerCollection = new AmavisServerCollection();
    }

    /**
     * Save the Amavis server names into the database (if not already saved)
     */
    private function saveAmavisServerNamesToDatabase() : void {
        $amavisServerModel = new AmavisServerModel();
        foreach ($this->amavisServerCollection->all() as $amavisServer) {
            $serverName = $amavisServer->getName();
            $serverExists = $amavisServerModel->entryExists([
                "name" => $serverName
            ]);
            if (!$serverExists)
                $amavisServerModel->create([
                    "name" => $serverName
                ]);
        }
    }

    /**
     * Set the config of the request email inbox and request email archive mailbox
     * @throws ExtendedException
     */
    private function setRequestMailboxConfig() : void {
        $this->requestMailboxConfig = new MailboxConfig(env("REQUEST_MAILBOX_IMAP_INBOX"),
            env("REQUEST_MAILBOX_IMAP_ARCHIVE"));
    }

    /**
     * Fetch unparsed request email data
     * @throws ExtendedException
     */
    private function fetchRequestEmails() : void {

        // Create new IMAP connection instance
        $this->imapConnection = new ImapConnection($this->getImapConfig());

        // Create new request email reader instance
        $requestEmailReader = new RequestEmailReader($this->imapConnection, $this->amavisServerCollection);

        // Set the request mailbox config
        $requestEmailReader->setMailboxConfig($this->requestMailboxConfig);

        // Connect to the request mailbox via IMAP
        $this->imapConnection->establish();

        // Get unread request email data
        $this->requestMessages = $requestEmailReader->read();
    }

    /**
     * Fill the email collection with unparsed request data, which then automatically retrieves and parses all remaining
     * needed information within its scope
     * @throws ExtendedException
     */
    private function fillEmailCollection() : void {

        // Create new SFTP connection instance for each Amavis server and save it to a collection of type
        // "App\Collections\ConnectionCollection"
        $sftpConnections = $this->getSftpConnections();
        $this->sftpConnectionCollection = new ConnectionCollection($sftpConnections);

        // Create objects to retrieve the remaining data
        $quarantineEmailReader = new QuarantineEmailReader($this->sftpConnectionCollection, $this->amavisServerCollection);
        $this->emailCollection = new EmailCollection($quarantineEmailReader);

        // Establish SFTP connection with all Amavis servers contained by "$this->amavisServerCollection"
        $this->sftpConnectionCollection->connectAll();

        // Create email objects from read data, which contains only the relevant data and add it to a collection
        foreach ($this->requestMessages as $requestMessage)
            $this->emailCollection->add($requestMessage);

        // Terminate all Amavis server SFTP connections
        $this->sftpConnectionCollection->disconnectAll();
    }

    /**
     * Save the retrieved and parsed data into the database
     * @throws ExtendedException
     * @throws ReflectionException
     */
    private function saveToDatabase() : void {
        $requestsModel = new RequestsModel();
        $this->databaseSavingResults = $requestsModel->saveRequests($this->emailCollection);
    }

    /**
     * Each request email where the corresponding request email - quarantine email group got successfully written
     * into the database, gets moved into the archive folder in the request mailbox
     * @throws ExtendedException
     */
    private function moveRequestEmails() : void {

        // Create new request email manipulator instance
        $requestEmailHandler = new RequestEmailHandler($this->imapConnection);

        // Set the request mailbox config
        $requestEmailHandler->setMailboxConfig($this->requestMailboxConfig);

        // Move saved emails to the archive folder
        $requestEmailHandler->archiveRequestEmails($this->databaseSavingResults);

        // Terminate the requests mailbox IMAP connection
        $this->imapConnection->terminate();
    }

    /**
     * Get the IMAP config for accessing the release request mailbox
     * @return array
     */
    private function getImapConfig() : array {
        return [
            "host" => env("REQUEST_MAILBOX_IMAP_HOST", "some.hostname"),
            "port" => env("REQUEST_MAILBOX_IMAP_PORT", 993),
            "encryption" => env("REQUEST_MAILBOX_IMAP_ENCRYPTION", "ssl"),
            "validate_cert" => env("REQUEST_MAILBOX_IMAP_VALIDATE_CERT", true),
            "username" => env("REQUEST_MAILBOX_IMAP_USERNAME", "some.user"),
            "password" => env("REQUEST_MAILBOX_IMAP_PASSWORD", "some.password"),
            "protocol" => env("REQUEST_MAILBOX_IMAP_PROTOCOL", "imap")
        ];
    }

    /**
     * Get the SFTP config for accessing an Amavis server
     * @param AmavisServer $amavisServer
     * @return array
     */
    private function getSftpConfig(AmavisServer $amavisServer) : array {
        return [
            "host" => $amavisServer->getIp(),
            "port" => $amavisServer->getSshPort(),
            "username" => $amavisServer->getSshUsername(),
            "password" => $amavisServer->getSshPassword()
        ];
    }

    /**
     * Get an SFTP connection for each Amavis server in "$this->amavisServerCollection"
     * @return array
     * @throws ExtendedException
     */
    private function getSftpConnections() : array {
        $sftpConnections = [];
        foreach ($this->amavisServerCollection->all() as $amavisServer) {
            $sftpConfig = $this->getSftpConfig($amavisServer);
            $sftpConnections[$amavisServer->getName()] = new SftpConnection($sftpConfig);
        }
        return $sftpConnections;
    }
}
