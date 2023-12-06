<?php


namespace App\Exceptions;


use App\Utils\OopUtil;
use Throwable;

class ExtendedException extends \Exception
{
    /**
     * ExtendedException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @throws static
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null) {

        // Specify class and method where the exception was thrown
        $callerInfo = OopUtil::getCallerInfo([
            "class",
            "function",
            "line"
        ]);
        $callerClass = $callerInfo["class"] ?? null;
        $callerFunction = $callerInfo["function"] ?? null;
        $callerLine = $callerInfo["line"] ?? null;
        $messagePrefix = ($callerClass && $callerFunction && $callerLine) ? "$callerClass::$callerFunction():$callerLine:" :
            null;

        // Construct the parent
        parent::__construct("$messagePrefix $message", $code, $previous);
    }

    /**
     * To string
     * @return string
     */
    public function __toString() : string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";
    }
}
