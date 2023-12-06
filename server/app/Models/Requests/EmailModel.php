<?php


namespace App\Models\Requests;


use App\Emails\Components\EmailComponent;
use App\Emails\Email;
use App\Exceptions\ExtendedException;
use App\Models\ExtendedModel;
use App\Utils\ArrayUtil;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

abstract class EmailModel extends ExtendedModel
{
    /**
     * Valid email dataset types
     */
    private const EMAIL_DATASET_TYPES = [
        Email::class,
        EmailComponent::class
    ];

    /**
     * Converting an "App\Emails\Email" object to an into the corresponding database table importable
     * dataset
     * @param array|Email|EmailComponent $emailDataset
     * @throws ExtendedException
     * @throws ReflectionException
     */
    protected function toImportableDataset(&$emailDataset) : void {
        $importableEmailDataset = [];

        // If '$emailDataset' is an array of email datasets, convert each sub dataset to an importable dataset
        if (is_array($emailDataset)) {
            foreach ($emailDataset as &$nestedDataset)
                $this->toImportableDataset($nestedDataset);
            return;
        }

        // Validate email dataset type
        $emailDatasetClass = $this->validateEmailDataset($emailDataset);

        // Get all functions of the email
        $reflectedEmail = new ReflectionClass($emailDatasetClass);
        $emailFunctions = $reflectedEmail->getMethods(ReflectionMethod::IS_PUBLIC);

        // Convert getter function names to database table column names
        // Example: "getDatabaseColumn" - "database_column"
        // The column names will be used as a key of the dataset, the returned value of the corresponding getter
        // function will represent the value
        foreach ($emailFunctions as $emailFunction) {

            // If getter function
            if (substr($emailFunction->getName(), 0, 3) === "get") {
                $getterFunctionName = $emailFunction->getName();

                // Remove the "get" at the beginning of the name
                $name = substr($getterFunctionName, 3);

                // Make the first character lowercase
                $name = lcfirst($name);

                // Put a underscore before each uppercase character
                $name = preg_replace("/([\p{Lu}])/", "_$1", $name);

                // Convert the whole name into lowercase
                $databaseColumnName = strtolower($name);

                // Add an importable key-value pair to the dataset
                $importableEmailDataset[$databaseColumnName] = $emailDataset->{$getterFunctionName}();
            }
        }

        // The email object has fulfilled its purpose, overwrite it with the importable dataset
        $emailDataset = $importableEmailDataset;
    }

    /**
     * If email dataset type invalid, throw exception
     * @param object|Email|EmailComponent $emailDataset
     * @return string
     * @throws ExtendedException
     */
    private function validateEmailDataset(object $emailDataset) : string {

        // If email dataset not an object, throw exception
        if (!is_object($emailDataset))
            throw new ExtendedException("Passed parameter '\$emailDataset' must be an object.");

        // Get class of email dataset object
        $emailDatasetClass = get_class($emailDataset);

        // Check if email dataset an instance of any valid type
        $matchingValidTypeFound = false;
        foreach (self::EMAIL_DATASET_TYPES as $validType) {
            if ($emailDataset instanceof $validType)
                $matchingValidTypeFound = true;
        }

        // If no matching valid type found, throw exception
        if (!$matchingValidTypeFound) {
            $validTypesString = ArrayUtil::toString(self::EMAIL_DATASET_TYPES);
            throw new ExtendedException("Passed parameter '\$emailDataset' must be one of following " .
                "types: '$validTypesString', '$emailDatasetClass', provided.");
        }

        return $emailDatasetClass;
    }
}
