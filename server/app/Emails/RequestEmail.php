<?php


namespace App\Emails;


use App\Exceptions\ExtendedException;

class RequestEmail extends Email
{
    /**
     * Timestamp
     * @var string
     */
    private $timestamp = "";

    /**
     * Amavis server name
     * @var string
     */
    private $amaivsServerName = "";

    /**
     * RequestEmail constructor
     * Must contain following attributes: "amavisIdentifier", "senderEmail", "senderName", "message", "timestamp",
     * "amavisServerName", else throw exception
     * @param array $attributes
     * @throws ExtendedException
     */
    public function __construct(array $attributes) {

        // Parent construction
        parent::__construct($attributes, [
            "timestamp",
            "amavisServerName"
        ]);

        // Set attributes
        $this->timestamp = $attributes["timestamp"];
        $this->amaivsServerName = $attributes["amavisServerName"];
    }

    /**
     * Get the timestamp
     * @return string
     */
    public function getTimestamp() : string {
        return $this->timestamp;
    }

    /**
     * Get the Amavis server name
     * @return string
     */
    public function getAmavisServerName() : string {
        return $this->amaivsServerName;
    }
}
