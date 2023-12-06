<?php


namespace App\Emails;


use App\Collections\AttachmentCollection;
use App\Emails\Components\QuarantineEmailAttachment;
use App\Exceptions\ExtendedException;

class QuarantineEmail extends Email
{
    /**
     * Subject  (can be null)
     * @var null|string
     */
    private $subject = "";

    /**
     * Attachments information
     * @var null|AttachmentCollection
     */
    private $attachments = null;

    /**
     * Foreign key of the Amavis server where the email is located on
     * @var null|int
     */
    private $fkAmavisServerId = null;

    /**
     * QuarantineEmail constructor
     * Must contain following attributes: "senderEmail", "senderName", "subject", "message", "attachments",
     * "amavisIdentifier", "fkAmavisServerId", else throw exception
     * @param array $attributes
     * @throws ExtendedException
     */
    public function __construct(array $attributes) {

        // Parent construction
        parent::__construct($attributes, [
            "string,NULL" => "subject",
            "array" => "attachments",
            "integer" => "fkAmavisServerId"
        ]);

        // Set attributes
        $this->subject = $attributes["subject"];
        $this->setAttachmentsInformation($attributes["attachments"]);
        $this->fkAmavisServerId = $attributes["fkAmavisServerId"];
    }

    /**
     * For each attachment information, create and save an
     * App\Emails\Components\QuarantineEmailAttachment' instance in
     * 'App\Collections\AttachmentCollection'
     * @param array $attachments
     * @throws ExtendedException
     */
    private final function setAttachmentsInformation(array $attachments) : void {
        $this->attachments = new AttachmentCollection();
        foreach ($attachments as $attachmentInformation) {
            $attachment = new QuarantineEmailAttachment($attachmentInformation["name"], $attachmentInformation["type"],
                $attachmentInformation["bytes"]);
            $this->attachments->add($attachment);
        }
    }

    /**
     * Get the subject
     * @return string|null
     */
    public function getSubject() : ?string {
        return $this->subject;
    }

    /**
     * Get the attachments information
     * @return AttachmentCollection
     */
    public function getAttachments() : AttachmentCollection {
        return $this->attachments;
    }

    /**
     * Get Amavis server foreign key
     * @return int|null
     */
    public function getFkAmavisServerId(): int {
        return $this->fkAmavisServerId;
    }
}
