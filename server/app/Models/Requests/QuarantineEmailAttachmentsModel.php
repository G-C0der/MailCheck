<?php

namespace App\Models\Requests;

use App\Exceptions\ExtendedException;
use App\Utils\FileSystemUtil;

class QuarantineEmailAttachmentsModel extends EmailModel
{
    /**
     * Database table
     * @var string
     */
    protected $table = "quarantine_email_attachments";

    /**
     * Columns that can be used in mass assignment
     * @var array
     */
    protected $fillable = [
        "name",
        "type",
        "bytes",
        "fk_quarantine_email_id"
    ];

    /**
     * Appended columns when row queried
     * @var array
     */
    protected $appends = [
        "size"
    ];

    /**
     * Determines that table doesn't has to contain the default timestamp columns
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get file in the appropriate unit
     * @return string
     * @throws ExtendedException
     */
    public function getSizeAttribute() : ?string {
        return $this->bytes ? FileSystemUtil::formatFileSize($this->bytes) : null;
    }
}
