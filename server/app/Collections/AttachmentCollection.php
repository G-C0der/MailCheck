<?php


namespace App\Collections;


use App\Emails\Components\QuarantineEmailAttachment;
use App\Exceptions\ExtendedException;

class AttachmentCollection extends Collection
{
    /**
     * The only valid types in this collection
     */
    private const VALID_TYPES = [
        QuarantineEmailAttachment::class
    ];

    /**
     * AttachmentCollection constructor
     * @param array $attachments
     * @throws ExtendedException
     */
    public function __construct(array $attachments = []) {

        // Parent construction - validates "$attachments"
        parent::__construct($attachments, self::VALID_TYPES);
    }

}
