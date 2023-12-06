<?php


namespace App\Utils;


use App\Exceptions\ExtendedException;

abstract class OopUtil
{
    /**
     * Get information of the caller
     * Examples: class, function, line, object, type, args, file
     * @param array $info
     * @param int $backTraceIdx
     * @return array
     * @throws ExtendedException
     */
    public static final function getCallerInfo(array $info = [ "class", "function", "line", "object", "type", "args", "file" ], int $backTraceIdx = 2) : array {

        // If info "class" not set, set it
        if (!in_array("class", $info))
            array_push($info, "class");

        // Get the backtrace
        $backtrace = debug_backtrace();

        // Get the information from the called spot
        $informationSet = [];
        foreach ($info as $inf)
            $informationSet[$inf] = $backtrace[$backTraceIdx][$inf];

        // +1 to idx because we have to account for calling this function
        for ($idx = 1; $idx < count($backtrace); $idx++) {

            // If it is set
            if (isset($backtrace[$idx]))

                // If a different information set
                if ($informationSet["class"] !== $backtrace[$idx]["class"])
                    return $informationSet;
        }

        // Throw exception if calling class not found in backtrace
        throw new ExtendedException("Calling class not found in backtrace");
    }
}
