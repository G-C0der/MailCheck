<?php


namespace App\Processors\Parsers;


use App\Overrides\Webklex\Message;
use App\Processors\Readers\RequestEmailReader;
use App\Exceptions\ExtendedException;

class RequestEmailParser extends Parser
{
    /**
     * The text after the sender message
     * Used to find the sender message in the whole message
     */
    private const NEEDLE_SENDER_MESSAGE = "-----Ursprüngliche Nachricht-----";

    /**
     * Parse the request email to relevant data
     * @param Message $email
     * @return array
     * @throws ExtendedException
     */
    public function parse($email) : array {

        // If email not an instance of 'App\Overrides\Webklex\Message', throw exception
        if (!$email instanceof Message) {
            $type = gettype($email);
            throw new ExtendedException("Argument '\$email' must be an instance of " .
                "'App\Overrides\Webklex\Message', '$type' provided.");
        }

        // Set current email
        $this->currentEmail = $email;

        // Parse the email
        $sender = $this->retrieveSender();
        $parsedData = [
            "amavisIdentifier" => $this->retrieveAmavisIdentifier(),
            "senderEmail" => $sender->mail,
            "senderName" => $sender->personal,
            "message" => $this->retrieveSenderMessage(),
            "timestamp" => $this->retrieveTimestamp(),
            "amavisServerName" => $email->getAmavisServerName()
        ];

        // Unset current email
        $this->currentEmail = null;

        // Return parsed data
        return $parsedData;
    }

    /**
     * Retrieve the Amavis identifier from the email
     * @return string
     * @throws ExtendedException
     */
    private function retrieveAmavisIdentifier() : string {

        // Retrieve the Amavis identifier from the email Message
        $emailMessage = $this->currentEmail->getTextBody();
        return $this->retrieveInformation($emailMessage, [ self::getAmavisIdentifierRegex() ],
            true, "Amavis identifier", "passed parameter \$email",
            true, 0);
    }

    /**
     * Retrieve the sender from the email
     * @return object
     */
    private function retrieveSender() : object {
        return $this->currentEmail->getSender()[0];
    }

    /**
     * Retrieve the sender message from the email
     * @return string
     */
    private function retrieveSenderMessage() : string {

        // Get the whole message
        $message = $this->currentEmail->getTextBody();

        // Get the index after the last sender message index in the whole message
        $senderMessageEndIdx = strpos($message, self::NEEDLE_SENDER_MESSAGE);

        // Get the sender message
        $senderMessage = substr($message, 0, $senderMessageEndIdx);

        // Return the sender message without whitespaces at start and end
        return trim($senderMessage);
    }

    /**
     * Retrieve timestamp from the email
     * @return string
     */
    private function retrieveTimestamp() : string {
        return $this->currentEmail->getDate()->format("Y-m-d H:i:s");
    }
}
