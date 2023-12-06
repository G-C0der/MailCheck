<?php


namespace App\Processors\Parsers;


use App\Exceptions\ExtendedException;
use App\Models\Requests\AmavisServerModel;

class QuarantineEmailParser extends Parser
{
    /**
     * Thea head of a content section in the body and attachments area of the email
     */
    protected const REGEX_CONTENT_HEAD = "--.*\s";

    /**
     * The body of the message wrappers
     */
    private const REGEX_MESSAGE_WRAPPER_BODY = "Content-Type: .*\sContent-Transfer-Encoding: .*";

    /**
     * Needle to find the start of the sender information (text right before sender information)
     */
    private const REGEX_NEEDLE_START_SENDER = "\s" . self::NEEDLE_FROM;

    /**
     * Needle to find the end of the sender name (text right after the sender name)
     */
    private const REGEX_NEEDLE_END_SENDER_NAME = " <";

    /**
     * Needle to find the string "From: "
     */
    private const NEEDLE_FROM = "From: ";

    /**
     * Min and max length of the sender email
     */
    private const SENDER_EMAIL_LENGTH = [
        "MIN" => 3,
        "MAX" => 320
    ];

    /**
     * Min and max length of the sender name
     */
    private const SENDER_NAME_LENGTH = [
        "MIN" => 0,
        "MAX" => 78
    ];

    /**
     * The name of the retrieval source of a single email attribute
     */
    private const ATTRIBUTE_RETRIEVAL_SOURCE_NAME = "fetched quarantine email data";

    /**
     * Parse the quarantine email to relevant data
     * @param array $emailData
     * @return array
     * @throws ExtendedException
     */
    public function parse($emailData) : array {

        // If email not of type "string", throw exception
        $this->validateArguments($emailData);

        // Set current email
        $this->currentEmail = $emailData["email"];

        // Parse the email
        $sender = $this->retrieveSender();
        $parsedData = [
            "senderEmail" => $this->retrieveSenderEmail($sender),
            "senderName" => $this->retrieveSenderName($sender),
            "subject" => $this->retrieveSubject(),
            "message" => $this->retrieveMessage(),
            "attachments" => $this->retrieveAttachmentInformation(),
            "amavisIdentifier" => $emailData["amavisIdentifier"],
            "fkAmavisServerId" => $this->getAmavisServerId($emailData["amavisServerName"])
        ];

        // Unset current email
        $this->currentEmail = "";

        // Return parsed data
        return $parsedData;
    }

    /**
     * Validate arguments passed to the "parse" or "parseAttachments" function
     * If "$email" parameter not empty, set it as "$this->currentEmail" attribute
     * @param array|string $emailData
     * @throws ExtendedException
     */
    protected function validateArguments($emailData) : void {
        $email = $emailData;

        // If email data contains other email information than the email itself (like Amavis identifier)
        if (is_array($email)) {

            // If no email set inside the email data, throw exception
            if (!isset($email["email"]))
                throw new ExtendedException("Provided array '\$emailData' must contain an 'email' property.");

            // If no Amavis identifier provided, throw exception
            if (!isset($email["amavisIdentifier"]))
                throw new ExtendedException("Provided array '\$emailData' must contain an " .
                    "'amavisIdentifier' property.");

            // Validate Amavis identifier
            self::validateAmavisIdentifier($email["amavisIdentifier"]);

            // If Amavis server name provided, throw exception
            if (!isset($email["amavisServerName"]))
                throw new ExtendedException("Provided array '\$emailData' must contain an " .
                    "'amavisServerName' property.");

            // Set the email
            $email = $email["email"];
        }

        // If email not of type "string", throw exception
        if (!is_string($email)) {
            $type = gettype($email);
            throw new ExtendedException("Argument '\$email' must be of type 'string', '$type' provided.");
        }

        // If no current email provided, throw exception
        // If passed email parameter not empty, set it as current email attribute
        if (strlen($email) > 0)
            $this->currentEmail = $email;
        else
            throw new ExtendedException("Passed parameter '\$email' must not contain an empty string.");
    }

    /**
     * Retrieve sender information from the email
     * @return string
     * @throws ExtendedException
     */
    private function retrieveSender() : string {

        // Get min & max length of the whole sender information
        $senderInformationLength = $this->getSenderInformationLength();

        // Retrieve the sender information from email with regex
        $sender = $this->retrieveInformation($this->currentEmail, [
                self::REGEX_NEEDLE_START_SENDER,
                ".{" . $senderInformationLength["min"] . "," . $senderInformationLength["max"] . "}"
            ], true,"Sender information",
            self::ATTRIBUTE_RETRIEVAL_SOURCE_NAME, true, 0);

        // Return sender information without whitespaces at end and beginning
        return trim($sender);
    }

    /**
     * Retrieve the sender email from the sender information
     * @param string $senderInformation
     * @return string
     * @throws ExtendedException
     */
    private function retrieveSenderEmail(string $senderInformation) : string {

        // Get min & max sender email length
        $senderEmailLength = $this->getSenderInformationLength([ "email" ]);

        // Return the parsed sender email
        return $this->retrieveInformation($senderInformation, [
                "<",
                ".{" . $senderEmailLength["min"] . "," . $senderEmailLength["max"] . "}",
                ">"
            ], true, "Sender email",
            self::ATTRIBUTE_RETRIEVAL_SOURCE_NAME);
    }

    /**
     * Retrieve the sender name from the email
     * @param string $senderInformation
     * @return string|null
     * @throws ExtendedException
     */
    private function retrieveSenderName(string $senderInformation) : ?string {

        // Get min & max sender name length
        $senderNameLength = $this->getSenderInformationLength([ "name" ]);

        // Retrieve parsed sender name
        $name = $this->retrieveInformation($senderInformation, [
                self::NEEDLE_FROM,
                ".{" . $senderNameLength["min"] . "," . $senderNameLength["max"] . "}?",
                self::REGEX_NEEDLE_END_SENDER_NAME
            ]);

        // Return the name or null, if the name is an empty string
        return strlen($name) > 0 ? $name : null;
    }

    /**
     * Retrieve the subject from the email
     * @return string
     */
    private function retrieveSubject() : string {

        // Get header of current email
        $header = $this->getHeader();

        // Return the subject in "ASCII" format
        return $this->decodeMimeHeaderExtension($header->subject, false);
    }

    /**
     * Retrieve the message from the email
     * @return string|null
     * @throws ExtendedException
     */
    private function retrieveMessage() : ?string {
        $message = [];

        // The regex to find the head of the message wrapper (message lies between head and foot wrapper)
        $messageHeadWrapperRegex = $this->buildRegex([
            self::REGEX_CONTENT_HEAD ,
            self::REGEX_MESSAGE_WRAPPER_BODY,
            "(\s{2})"
        ], "", "");

        // The regex to find the foot of the message wrapper (message lies between head and foot wrapper)
        $messageFootWrapperRegex = $this->buildRegex([
            self::REGEX_CONTENT_HEAD,
            self::REGEX_MESSAGE_WRAPPER_BODY
        ], "", "");

        // The regex for querying the message, including its queried wrapper
        $messageRegex = $this->buildRegex([
            $messageHeadWrapperRegex,
            self::REGEX_ALL,
            $messageFootWrapperRegex
        ]);

        // Querying the message
        preg_match($messageRegex, $this->currentEmail, $message);

        // If the message not found, there is most likely no message, so return null
        // Use index "4", because the regex group which represents the message only is at index "4"
        if (!isset($message[4]))
            return null;

        // Return the quoted-printable decoded and trimmed message
        // Use index "4", because the regex group which represents the message only is at index "4"
        $message = quoted_printable_decode($message[4]);
        return  trim($message);
    }

    /**
     * Retrieve the needed information from all attachments of the email
     * @return array
     * @throws ExtendedException
     */
    private function retrieveAttachmentInformation() : array {
        $quarantineEmailAttachmentsParser = new QuarantineEmailAttachmentsParser();
        return $quarantineEmailAttachmentsParser->parse($this->currentEmail);
    }

    /**
     * Get the Amavis server id from name
     * @param string $amavisServerName
     * @return int
     * @throws ExtendedException
     */
    private function getAmavisServerId(string $amavisServerName) : int {
        $amavisServerModel = new AmavisServerModel();
        $id =  $amavisServerModel->querySpecific($amavisServerModel->getKeyName(), [
            [ "name", $amavisServerName ]
        ], true);

        // If Amavis server not found in database with its name, throw exception
        if (!$id)
            throw new ExtendedException("Amavis server with the name '$amavisServerName' not found in the " .
                "database");

        // Return the found id
        return (int) $id;
    }

    /**
     * Get header of the current email
     * @return object
     */
    private function getHeader() : object {
        return imap_rfc822_parse_headers($this->currentEmail);
    }

    /**
     * Get the min and max length of the whole sender information string
     * @param array $information
     * @return array
     */
    private function getSenderInformationLength(array $information = [ "email", "name" ]) : array {
        $min = 0;
        $max = 0;

        // Possible sender information
        $validInformation = [
            "email",
            "name"
        ];

        // Add email min & max length to min & max if email information specified
        if (in_array("email", $information)) {
            $min += self::SENDER_EMAIL_LENGTH["MIN"];
            $max += self::SENDER_EMAIL_LENGTH["MAX"];
        }

        // Add name min & max length to min & max if name information specified
        if (in_array("name", $information)) {
            $min += self::SENDER_NAME_LENGTH["MIN"];
            $max += self::SENDER_NAME_LENGTH["MAX"];
        }

        // If specified information contains all valid information (whole information length required), add the length
        // of all other characters together (which can be found in the whole sender information) to min & max
        $differences = array_diff($validInformation, $information);
        if (sizeof($differences) <= 0) {
            $otherCharacters = "< >";
            $otherCharactersLength = strlen($otherCharacters);

            // -1 because the whitespace between "name" and "<email@example.com>" gets removed, when "name" is empty
            $min += ($otherCharactersLength - 1);
            $max += $otherCharactersLength;
        }

        // Return min & max of the sum of each specified sender information
        return [
            "min" => $min,
            "max" => $max
        ];
    }

    /**
     * Decodes "Multipurpose Internet Mail Extensions" to ASCII format
     * @param string $extension
     * @param bool $lineBreaks
     * @return string
     */
    private function decodeMimeHeaderExtension(string &$extension, $lineBreaks = true) : string {
        $decodedExtension = "";

        // "imap_mime_header_decode" returns an array with a sub array for each line break in the encoded extension
        $asciiExtensionParts = imap_mime_header_decode($extension);

        // Merge the encoded string parts to one string
        foreach ($asciiExtensionParts as $decodedExtensionPart) {

            // Add line breaks if specified
            $decodedExtension = $lineBreaks ? $decodedExtension . PHP_EOL . $decodedExtensionPart->text :
                $decodedExtension . $decodedExtensionPart->text;
        }

        // Overwrite "$extension" reference
        $extension = $decodedExtension;
        return $extension;
    }
}
