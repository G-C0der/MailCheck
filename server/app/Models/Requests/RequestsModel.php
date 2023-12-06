<?php


namespace App\Models\Requests;


use App\Collections\EmailCollection;
use App\Emails\QuarantineEmail;
use App\Emails\RequestEmail;
use App\Processors\EmailProcessor;
use App\Exceptions\ExtendedException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionException;

class RequestsModel extends RequestEmailModel
{
    /**
     * Data from associated models
     * @var array
     */
    protected $with = [
        "quarantineEmail"
    ];

    /**
     * Quarantine email relation
     * @return HasOne
     */
    public function quarantineEmail() : HasOne {
        return $this->hasOne(

            // Related
            "App\Models\Requests\QuarantineEmailModel",

            // Foreign key
            "fk_request_email_id",

            // Local key
            $this->primaryKey
        );
    }

    /**
     * Save all collected requests
     * Includes request email and corresponding quarantine email
     * @param EmailCollection $emailCollection
     * @return array
     * @throws ReflectionException
     * @throws ExtendedException
     */
    public function saveRequests(EmailCollection $emailCollection) : array {

        // Contains the Amavis identifier of each email pair, which was successfully written into the database
        $results = [];

        // Iterating over each request - quarantine email pair
        foreach ($emailCollection->all() as $emailPair) {

            // Retrieve the request and quarantine email from the email pair
            $requestEmail = $emailPair->getRequestEmail();
            $quarantineEmail = $emailPair->getQuarantineEmail();

            $amavisIdentifier = $this->saveRequest($requestEmail, $quarantineEmail);
            if (EmailProcessor::isAmavisIdentifier($amavisIdentifier))
                $results[] = $amavisIdentifier;
        }

        // Return the Amavis identifiers of successfully added requests
        return $results;
    }

    /**
     * Save a single request
     * Returns the Amavis identifier of the request on success, else null
     * @param RequestEmail $requestEmail
     * @param QuarantineEmail $quarantineEmail
     * @return string|null
     * @throws ExtendedException
     * @throws ReflectionException
     */
    private function saveRequest(RequestEmail $requestEmail, QuarantineEmail $quarantineEmail) : ?string {

        // Retrieve needed attributes from the passed emails
        $quarantineEmailAttachments = $quarantineEmail->getAttachments()->all();
        $amavisIdentifier = $requestEmail->getAmavisIdentifier();

        // Validate request
        if ($this->requestExists($amavisIdentifier))
            throw new ExtendedException("Request associated with Amavis identifier '$amavisIdentifier', " .
                "already present in database.");

        // Convert the retrieved email objects to a database importable dataset
        $this->toImportableDataset($requestEmail);
        $this->toImportableDataset($quarantineEmail);
        $this->toImportableDataset($quarantineEmailAttachments);

        // Write the email pair into the database
        $quarantineEmailAttachmentsModels = null;
        DB::transaction(function () use ($requestEmail, $quarantineEmail, $quarantineEmailAttachments,
            &$quarantineEmailAttachmentsModels) {

            // Insert the request email
            $requestEmailModel = $this->create($requestEmail);

            // Insert the quarantine email
            $quarantineEmailModel = $requestEmailModel->quarantineEmail()->create($quarantineEmail);

            // Insert the quarantine email attachments
            $quarantineEmailAttachmentsModels = $quarantineEmailModel->attachments()
                ->createMany($quarantineEmailAttachments);
        });

        // If the successfully inserted attachment count is the same as the importable attachment data count,
        // we know all attachments were inserted successfully. Therefore we also know that the quarantine email
        // data and therefore also the request email data was inserted successfully
        // As a result we will add the Amavis identifier of the request-quarantine email pair to the result set
        $successfullyInsertedAttachmentsCount = $this->getAttachmentsSuccessCount($quarantineEmailAttachmentsModels);
        return $successfullyInsertedAttachmentsCount === sizeof($quarantineEmailAttachments) ? $amavisIdentifier : null;
    }

    /**
     * Check if request exists in database
     * @param string $amavisIdentifier
     * @return bool
     * @throws ExtendedException
     */
    public function requestExists(string $amavisIdentifier) : bool {

        // Validate Amavis identifier
        EmailProcessor::validateAmavisIdentifier($amavisIdentifier);

        // Return if request exists
        return $this->entryExists([
            "amavis_identifier" => $amavisIdentifier
        ]);
    }

    /**
     * Get the number of successfully inserted attachments
     * @param Collection $quarantineEmailAttachmentsModels
     * @return int
     */
    private function getAttachmentsSuccessCount(Collection $quarantineEmailAttachmentsModels) : int {

        // Count the successfully inserted attachments
        $successfullyInsertedAttachmentsCount = 0;
        foreach ($quarantineEmailAttachmentsModels->all() as $quarantineEmailAttachmentsModel) {

            // If the id of the inserted attachment is numeric, we know it was successfully inserted
            if ($this->isValidId($quarantineEmailAttachmentsModel->pk_id))
                $successfullyInsertedAttachmentsCount++;
        }
        return $successfullyInsertedAttachmentsCount;
    }

    /**
     * Set the request to status "done"
     * @param string $amavisIdentifier
     * @return bool
     * @throws ExtendedException
     */
    public function setRequestDone(string $amavisIdentifier) : bool {
        $statusDone = $this->getStatusFromName(self::STATUS_NAME_DONE);
        return $this->changeStatus($amavisIdentifier, $statusDone);
    }
}
