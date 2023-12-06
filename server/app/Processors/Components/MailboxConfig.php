<?php


namespace App\Processors\Components;


use App\Exceptions\ExtendedException;

class MailboxConfig
{
    /**
     * Source folder name
     * @var string
     */
    private $sourceFolderName = "";

    /**
     * Target folder name
     * @var string
     */
    private $targetFolderName = "";

    /**
     * MailboxConfig constructor
     * @param string $sourceFolderName
     * @param string $targetFolderName
     * @throws ExtendedException
     */
    public function __construct(?string $sourceFolderName, ?string $targetFolderName) {

        // If a folder name is null, throw exception
        if (!isset($sourceFolderName) || !isset($targetFolderName))
            throw new ExtendedException("Passed parameters '\$sourceFolderName' and '\$targetFolderName' " .
                "cannot contain null.");

        // If a folder name is an empty string, throw exception
        if (strlen($sourceFolderName) <= 0 || strlen($targetFolderName) <= 0)
            throw new ExtendedException("Passed parameters '\$sourceFolderName' and '\$targetFolderName' " .
                "cannot contain an empty string.");

        // Set folders names
        $this->sourceFolderName = $sourceFolderName;
        $this->targetFolderName = $targetFolderName;
    }

    /**
     * Get source folder name
     * The uppercase format of the folder name is necessary for querying with the Webklex IMAP API
     * @param bool $originalFormat
     * @return string
     */
    public function getSourceFolderName($originalFormat = false) : string {
        return $originalFormat ? $this->sourceFolderName : strtoupper($this->sourceFolderName);
    }

    /**
     * Get target folder name
     * The uppercase format of the folder name is necessary for querying with the Webklex IMAP API
     * @param bool $originalFormat
     * @return string
     */
    public function getTargetFolderName($originalFormat = false) : string {
        return $originalFormat ? $this->targetFolderName : strtoupper($this->targetFolderName);
    }
}
