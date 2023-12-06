<?php


namespace App\Processors\Parsers;


use App\Exceptions\ExtendedException;
use App\Overrides\Webklex\Message;
use App\Processors\EmailProcessor;
use App\Utils\OopUtil;

abstract class Parser extends EmailProcessor implements ParserInterface
{
    /**
     * Email currently getting parsed
     * @var Message|string
     */
    protected $currentEmail = "";

    /**
     * Retrieve information from given string "$retrieveFrom" with provided regexes "$regexParts"
     * @param string $retrievalSource
     * @param array $regexParts
     * @param bool $throwException
     * @param string $informationName
     * @param string $retrievalSourceName
     * @param bool $firstOccurrence
     * @param int $informationGroupIdx
     * @return array|string|null
     * @throws ExtendedException
     */
    protected final function retrieveInformation(string $retrievalSource, array $regexParts, bool $throwException = false,
                                           string $informationName = "", string $retrievalSourceName = "",
                                           bool $firstOccurrence = true, int $informationGroupIdx = 2) {
        $information = [];

        // Regex to find the information
        $informationRegex = self::buildRegex($regexParts);

        // Querying the information
        $firstOccurrence ? preg_match($informationRegex, $retrievalSource, $information) :
            preg_match_all($informationRegex, $retrievalSource, $information);

        // If the information not found, throw exception (if "$throwException" is true, else return null)
        // Use index "2", because the regex group which represents the information only is at index "2"
        if (!isset($information[$informationGroupIdx]))
            if ($throwException)
                throw new ExtendedException("$informationName not found with regex '$informationRegex' in " .
                    "$retrievalSourceName.");
            else
                return null;

        // Return relevant information only
        // Use index "$informationGroupIdx", because the regex group which represents the information only is at index
        // "$informationGroupIdx"
        return $information[$informationGroupIdx];
    }
}
