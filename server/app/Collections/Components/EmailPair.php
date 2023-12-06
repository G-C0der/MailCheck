<?php


namespace App\Collections\Components;


use App\Emails\QuarantineEmail;
use App\Emails\RequestEmail;
use App\Exceptions\ExtendedException;

class EmailPair
{
    /**
     * Request email
     * @var null|RequestEmail
     */
    private $requestEmail = null;

    /**
     * Quarantine email
     * @var null|QuarantineEmail
     */
    private $quarantineEmail = null;

    /**
     * EmailPair constructor
     * @param RequestEmail $requestEmail
     * @param QuarantineEmail $quarantineEmail
     * @throws ExtendedException
     */
    public function __construct(RequestEmail $requestEmail, QuarantineEmail $quarantineEmail) {

        // Validate that the email pair matches
        $this->validateRelation($requestEmail, $quarantineEmail);

        // Set attributes
        $this->requestEmail = $requestEmail;
        $this->quarantineEmail = $quarantineEmail;
    }

    /**
     * Validate the relation of the two emails via Amavis identifier
     * @param RequestEmail $requestEmail
     * @param QuarantineEmail $quarantineEmail
     * @throws ExtendedException
     */
    private function validateRelation(RequestEmail $requestEmail, QuarantineEmail $quarantineEmail) : void {

        // If the emails doesn't contain the same Amavis identifier, throw exception
        if ($requestEmail->getAmavisIdentifier() !== $quarantineEmail->getAmavisIdentifier())
            throw new ExtendedException("Email pair invalid. Passed parameters '\$requestEmail' and " .
                "'\$quarantineEmail' must contain the same Amavis identifier.");
    }

    /**
     * Get request email
     * @return RequestEmail
     */
    public function getRequestEmail(): RequestEmail {
        return $this->requestEmail;
    }

    /**
     * Get quarantine email
     * @return QuarantineEmail
     */
    public function getQuarantineEmail(): QuarantineEmail {
        return $this->quarantineEmail;
    }
}
