<?php


namespace App\Processors\Parsers;


use App\Exceptions\ExtendedException;

class QuarantineEmailAttachmentsParser extends QuarantineEmailParser
{
    /**
     * Regex needle to find the start of an attachment section in the email
     */
    private const REGEX_NEEDLE_START_ATTACHMENT = "Content-Type: ";

    /**
     * Regex to find the content type "application"
     */
    private const REGEX_CONTENT_TYPE_APPLICATION = "application\/";

    /**
     * Regex needle to find the end of an attachment information section in the email
     */
    private const REGEX_NEEDLE_END_ATTACHMENT = "\sContent-Transfer-Encoding: ";

    /**
     * Regex to find a single attachment attribute (except attachment name) inside the attachment information
     * (must be used between the appropriate regex needles)
     */
    private const REGEX_ATTACHMENT_ATTRIBUTE = ".*";

    /**
     * Regex needle to find the start of an attachment name section in the attachment information
     */
    private const REGEX_NEEDLE_START_ATTACHMENT_NAME = "filename=\"?";

    /**
     * Regex needle to find an attachment name
     */
    private const REGEX_NEEDLE_ATTACHMENT_NAME =  "[^<>:;,?\"*|\/]+";

    /**
     * Regex needle to find the end of an attachment name section in the attachment information
     */
    private const REGEX_NEEDLE_END_ATTACHMENT_NAME = "\"?";

    /**
     * Regex needle to find the start of an attachment type section in the attachment information
     */
    private const REGEX_NEEDLE_START_ATTACHMENT_TYPE = self::REGEX_NEEDLE_START_ATTACHMENT;

    /**
     * Regex needle to find the end of an attachment type section in the attachment information
     */
    private const REGEX_NEEDLE_END_ATTACHMENT_TYPE = ";";

    /**
     * Regex needle to find the start of an attachment size section in the attachment information
     */
    private const REGEX_NEEDLE_START_ATTACHMENT_SIZE = "size=";

    /**
     * Regex needle to find the end of an attachment size section in the attachment information
     */
    private const REGEX_NEEDLE_END_ATTACHMENT_SIZE = ";";

    /**
     * The name of the retrieval source of a single attachment attribute
     */
    private const ATTRIBUTE_RETRIEVAL_SOURCE_NAME = "passed parameter '\$attachmentInformation'";

    /**
     * Parse the quarantine email attachments to relevant data
     * @param string $email
     * @return array
     * @throws ExtendedException
     */
    public function parse($email): array {

        // Validate arguments
        $this->validateArguments($email);

        // Set current email
        $this->currentEmail = $email;

        // Parse the attachments information
        $attachmentsInformation = $this->retrieveAttachmentsInformation();

        // Unset current email
        $this->currentEmail = "";

        // Return the parsed data
        return $attachmentsInformation;
    }

    /**
     * Retrieve relevant attachments information from the email
     * @return array
     * @throws ExtendedException
     */
    private function retrieveAttachmentsInformation() : array {

        // Retrieve attachments information from email
        $attachmentsInformation = $this->retrieveInformation($this->currentEmail, [
                parent::REGEX_CONTENT_HEAD,
                self::REGEX_NEEDLE_START_ATTACHMENT. self::REGEX_CONTENT_TYPE_APPLICATION . parent::REGEX_ALL,
                self::REGEX_NEEDLE_END_ATTACHMENT
            ], true, "Attachments information",
            "fetched quarantine email data", false);

        if (sizeof($attachmentsInformation) <= 0)
            throw new ExtendedException("No attachments information found in provided quarantine email data.");

        // Querying the relevant data from the queried attachments information and save it with a structure in array
        // Use index "2", because the regex group which represents the attachments information only is at index "2"
        $relevantAttachmentsInformation = [];
        foreach ($attachmentsInformation as $attachmentInformation) {
            $relevantAttachmentsInformation[] = [
                "name" => $this->retrieveAttachmentName($attachmentInformation),
                "type" => $this->retrieveAttachmentType($attachmentInformation),
                "bytes" => $this->retrieveAttachmentBytes($attachmentInformation)
            ];
        }

        // Return parsed attachments information
        return $relevantAttachmentsInformation;
    }

    /**
     * Retrieve the attachment name from the attachment information
     * @param string $attachmentInformation
     * @return string
     * @throws ExtendedException
     */
    private function retrieveAttachmentName(string $attachmentInformation) : string {
        return $this->retrieveInformation($attachmentInformation, [
            self::REGEX_NEEDLE_START_ATTACHMENT_NAME,
            self::REGEX_NEEDLE_ATTACHMENT_NAME,
            self::REGEX_NEEDLE_END_ATTACHMENT_NAME
        ], true,"Attachment name",
            self::ATTRIBUTE_RETRIEVAL_SOURCE_NAME);
    }

    /**
     * Retrieve the attachment type from the attachment information
     * @param string $attachmentInformation
     * @return string
     * @throws ExtendedException
     */
    private function retrieveAttachmentType(string $attachmentInformation) : string {
        return $this->retrieveInformation($attachmentInformation, [
            self::REGEX_NEEDLE_START_ATTACHMENT_TYPE,
            self::REGEX_ATTACHMENT_ATTRIBUTE,
            self::REGEX_NEEDLE_END_ATTACHMENT_TYPE
        ], true,"Attachment type",
            self::ATTRIBUTE_RETRIEVAL_SOURCE_NAME);
    }

    /**
     * Retrieve the attachment size in bytes from the attachment information
     * @param string $attachmentInformation
     * @return float|null
     * @throws ExtendedException
     */
    private function retrieveAttachmentBytes(string $attachmentInformation) : ?float {
        $bytes = $this->retrieveInformation($attachmentInformation, [
            self::REGEX_NEEDLE_START_ATTACHMENT_SIZE,
            self::REGEX_ATTACHMENT_ATTRIBUTE,
            self::REGEX_NEEDLE_END_ATTACHMENT_SIZE
        ]);
        return $bytes ? (float) $bytes : null;
    }
}
