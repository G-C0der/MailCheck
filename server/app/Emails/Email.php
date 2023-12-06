<?php


namespace App\Emails;


use App\Processors\EmailProcessor;
use App\Exceptions\ExtendedException;
use App\Utils\ArrayUtil;

abstract class Email
{
    /**
     * Amavis identifier
     * @var string
     */
    private $amavisIdentifier = "";

    /**
     * Sender email
     * @var string
     */
    private $senderEmail = "";

    /**
     * Sender name
     * @var string
     */
    private $senderName = "";

    /**
     * The email message
     * @var string
     */
    private $message = "";

    /**
     * Email constructor
     * @param array $attributes
     * The attributes required by the child class
     * @param array $requiredAttributes
     * @param array $defaultRequiredTypes
     * @throws ExtendedException
     */
    public function __construct(array $attributes, array $requiredAttributes,
                                array $defaultRequiredTypes = [ "string" ]) {

        // Merge the required attributes of the child class with the required attributes of this class, if attributes of
        // this class required
        $requiredAttributes = array_merge($requiredAttributes, [
            "amavisIdentifier",
            "senderEmail",
            "senderName",
            "string,NULL" => "message"
        ]);

        // Validate attributes of this and the child class
        ArrayUtil::validateAttributes($attributes, $requiredAttributes, $defaultRequiredTypes);

        // Validate Amavis identifier
        EmailProcessor::validateAmavisIdentifier($attributes["amavisIdentifier"]);

        // Set attributes
        $this->amavisIdentifier = $attributes["amavisIdentifier"];
        $this->senderEmail = $attributes["senderEmail"];
        $this->senderName = $attributes["senderName"];
        $this->message = $attributes["message"];
    }

    /**
     * Get the Amavis identifier
     * @return string
     */
    public final function getAmavisIdentifier() : string {
        return $this->amavisIdentifier;
    }

    /**
     * Get the sender email
     * @return string
     */
    public final function getSenderEmail() : string {
        return $this->senderEmail;
    }

    /**
     * Get the sender name
     * @return string
     */
    public final function getSenderName() : string {
        return $this->senderName;
    }

    /**
     * Get the message
     * @return string|email
     */
    public final function getMessage() : ?string {
        return $this->message;
    }
}
