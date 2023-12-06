<?php


namespace App\Processors;


use App\Exceptions\ExtendedException;

abstract class EmailProcessor
{
    /**
     * The regex to find every character
     * Operator "*" made non-greedy with the "?" operator
     */
    protected const REGEX_ALL = "[\S\s]*?";

    /**
     * A regex character set which can be used in regex pattern
     */
    protected const REGEX_PATTERN_DIGITS_AND_LETTERS = "0-9a-zA-Z";

    /**
     * The static part of an Amavis identifier
     */
    protected const NEEDLE_AMAVIS_IDENTIFIER = "\/banned-";

    /**
     * Build a regex from the provided regex list
     * The regexes will be concatenated depending on the order inside the list
     * Each regex will be put inside a group, if not already a group
     * Delimiters will be added, if not already added
     * @param array $regexParts
     * @param string $modifier
     * @param string $delimiter
     * @return string
     * @throws ExtendedException
     */
    protected static final function buildRegex(array $regexParts, string $modifier = "", string $delimiter = "/") : string {

        // Return regex if only one regex provided and regex already has delimiters
        if (sizeof($regexParts) === 1) {
            $regex = array_values($regexParts)[0];
            if (self::hasDelimiters($regex))
                return $regex;
        }

        $completeRegex = "";

        // Concatenating the regex parts into one regex and putting each part into a group
        foreach ($regexParts as $regexPart) {

            // If regex part not a string, throw exception
            if (!is_string($regexPart)) {
                $type = gettype($regexPart);
                throw new ExtendedException("Each regex part inside passed array '\$regexParts' must be of " .
                    "type 'string'. '$type' found.");
            }

            // Append the grouped regex part to the (not jet) complete regex
            $completeRegex .= self::toGroup($regexPart);
        }

        // Returning the complete regex with delimiters and passed modifier
        return self::addDelimiters($completeRegex, $delimiter) . $modifier;
    }

    /**
     * Convert a regex to a group, if not already a group
     * @param string $regex
     * @return string
     */
    private static final function toGroup(string $regex) : string {

        // Check if regex already is a group
        $isGroup = $regex[0] === "(" && $regex[-1] === ")";

        // Return the regex as a group
        return $isGroup ? $regex : "($regex)";
    }

    /**
     * Add delimiters to a regex, if not already has the specified delimiters
     * @param string $regex
     * @param string $delimiter
     * @return string
     */
    private static final function addDelimiters(string $regex, string $delimiter = "/") : string {

        // Check if the regex already has the specified delimiters
        $hasDelimiters = self::hasDelimiters($regex, $delimiter);

        // Return the regex with the specified delimiters
        return $hasDelimiters ? $regex : $delimiter . $regex . $delimiter;
    }

    /**
     * Check if regex has delimiters
     * @param string $regex
     * @param string $delimiter
     * @return bool
     */
    private static final function hasDelimiters(string $regex, string $delimiter = "/") : bool {
        return $regex[0] === $delimiter && $regex[-1] === $delimiter;
    }

    /**
     * Get the regex for querying an Amavis identifier
     * Example of an Amavis identifier: "9/banned-9M-Ns_tDl7ia"
     * @return string
     * @throws ExtendedException
     */
    public static final function getAmavisIdentifierRegex() : string {
        return self::buildRegex([
            "[" . self::REGEX_PATTERN_DIGITS_AND_LETTERS . "]",
            self::NEEDLE_AMAVIS_IDENTIFIER,
            "[" . self::REGEX_PATTERN_DIGITS_AND_LETTERS . "_-]{12}"
        ]);
    }

    /**
     * Check if a valid Amavis identifier
     * @param string|null $val
     * @return bool
     * @throws ExtendedException
     */
    public static final function isAmavisIdentifier(?string $val) : bool {
        if (is_null($val))
            return false;
        return preg_match(self::getAmavisIdentifierRegex(), $val) === 1;
    }

    /**
     * If Amavis identifier not valid, throw exception
     * @param string|null $amavisIdentifier
     * @throws ExtendedException
     */
    public static final function validateAmavisIdentifier(?string $amavisIdentifier) : void {
        if(!self::isAmavisIdentifier($amavisIdentifier))
            throw new ExtendedException("Invalid Amavis identifier '$amavisIdentifier'.");
    }
}
