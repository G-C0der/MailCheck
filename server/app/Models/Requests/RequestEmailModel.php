<?php

namespace App\Models\Requests;


use App\Exceptions\ExtendedException;

abstract class RequestEmailModel extends EmailModel
{
    /**
     * Database table
     * @var string
     */
    protected $table = "request_email";

    /**
     * Columns that can be used in mass assignment
     * @var array
     */
    protected $fillable = [
        "amavis_identifier",
        "sender_email",
        "sender_name",
        "message",
        "timestamp",
        "status"
    ];

    /**
     * Appended columns when row queried
     * @var array
     */
    protected $appends = [
        "status_name",
        "status_icon",
        "status_tooltip"
    ];

    /**
     * Status name pending
     */
    protected const STATUS_NAME_PENDING = "pending";

    /**
     * Status name done
     */
    protected const STATUS_NAME_DONE = "done";

    /**
     * Attributes of the status
     * Mainly for icon rendering in the frontend
     */
    private const STATUS_ATTRIBUTES_LIST = [

        // Status pending
        1 => [ "name" => self::STATUS_NAME_PENDING, "icon" => "x-fa fa-hourglass", "tooltip" => "In Bearbeitung" ],

        // Status done
        2 => [ "name" => self::STATUS_NAME_DONE, "icon" => "x-fa fa-check", "tooltip" => "Erledigt" ]
    ];

    /**
     * Get status name from status
     * @param string $statusName
     * @return int|null
     */
    public function getStatusFromName(string $statusName) : ?int {
        foreach (self::STATUS_ATTRIBUTES_LIST as $status => $statusAttributes) {
            if ($statusAttributes["name"] === $statusName)
                return $status;
        }
        return null;
    }

    /**
     * Get status attribute from status
     * @param string $attribute
     * @return string|null
     */
    private function getStatusAttr(string $attribute) : ?string {
        return $this->status ? self::STATUS_ATTRIBUTES_LIST[$this->status][$attribute] : null;
    }

    /**
     * For status identification in the frontend
     * @return string|null
     */
    public function getStatusNameAttribute() : ?string {
        return $this->getStatusAttr("name") ?? null;
    }

    /**
     * For status icon rendering in the frontend
     * @return string|null
     */
    public function getStatusIconAttribute() : ?string {
        return $this->getStatusAttr("icon") ?? null;
    }

    /**
     * For status tooltip rendering in the frontend
     * @return string|null
     */
    public function getStatusTooltipAttribute() : ?string {
        return $this->getStatusAttr("tooltip") ?? null;
    }

    /**
     * Change the status
     * @param string $amavisIdentifier
     * @param int $status
     * @return bool
     * @throws ExtendedException
     */
    protected function changeStatus(string $amavisIdentifier, int $status) : bool {

        // If status doesnt exist, throw exception
        $statusList = array_keys(self::STATUS_ATTRIBUTES_LIST);
        if (!in_array($status, $statusList))
            throw new ExtendedException("Status '$status' not found.");

        // Changing the status
        return $this->where("amavis_identifier", $amavisIdentifier)->update([
            "status" => $status
        ]);
    }
}
