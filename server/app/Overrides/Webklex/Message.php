<?php


namespace App\Overrides\Webklex;


use Webklex\IMAP\Client;
use Webklex\IMAP\Exceptions\ConnectionFailedException;
use Webklex\IMAP\Exceptions\InvalidMessageDateException;

class Message extends \Webklex\IMAP\Message
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
     * Message constructor
     * @param int $uid
     * @param int|null $msglist
     * @param Client $client
     * @param string $amavisServerName
     * @param string $targetEmailType
     * @param int|null $fetch_options
     * @param bool $fetch_body
     * @param bool $fetch_attachment
     * @param bool $fetch_flags
     * @throws ConnectionFailedException
     * @throws InvalidMessageDateException
     */
    public function __construct(int $uid, ?int $msglist, Client $client, string $amavisServerName,
                                string $targetEmailType, ?int $fetch_options = null, bool $fetch_body = false,
                                bool $fetch_attachment = false, bool $fetch_flags = false) {

        // Set Amavis server
        $this->amavisServerName = $amavisServerName;

        // Set target email type
        $this->targetEmailType = $targetEmailType;

        // Parent construction
        parent::__construct($uid, $msglist, $client, $fetch_options, $fetch_body, $fetch_attachment, $fetch_flags);
    }

    /**
     * Get Amavis server name from where the "email was isolated message" originally was sent
     * @return string
     */
    public function getAmavisServerName() : string {
        return $this->amavisServerName;
    }

    /**
     * Get the class which represents the desired target format of the message
     * @return string
     */
    public function getTargetEmailType() : string {
        return $this->targetEmailType;
    }
}
