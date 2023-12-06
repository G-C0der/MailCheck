<?php

namespace App\Models\Requests;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuarantineEmailModel extends EmailModel
{
    /**
     * Database table
     * @var string
     */
    protected $table = "quarantine_email";

    /**
     * Columns that can be used in mass assignment
     * @var array
     */
    protected $fillable = [
        "sender_email",
        "sender_name",
        "subject",
        "message",
        "fk_request_email_id",
        "fk_amavis_server_id"
    ];

    /**
     * Data from associated models
     * @var array
     */
    protected $with = [
        "attachments",
        "amavisServer"
    ];

    /**
     * Attachments relation
     * @return HasMany
     */
    public function attachments() : HasMany {
        return $this->hasMany(

            // Related
            "App\Models\Requests\QuarantineEmailAttachmentsModel",

            // Foreign key
            "fk_quarantine_email_id",

            // Local key
            $this->primaryKey
        )->orderBy("type", "desc")
            ->orderBy("name", "desc")
            ->orderBy("bytes", "desc");
    }

    /**
     * Amavis server relation
     * @return BelongsTo
     */
    public function amavisServer() : BelongsTo {
        return $this->belongsTo(

            // Related
            "App\Models\Requests\AmavisServerModel",

            // Foreign key
            "fk_amavis_server_id",

            // Owner key
            "pk_id"
        );
    }
}
