<?php


namespace App\Factories;


use App\Emails\Email;
use App\Emails\QuarantineEmail;
use App\Emails\RequestEmail;
use App\Overrides\Webklex\Message;
use App\Processors\Parsers\QuarantineEmailParser;
use App\Processors\Parsers\RequestEmailParser;
use App\Exceptions\ExtendedException;

class EmailFactory
{
    /**
     * Email types and associated parser attributes
     * @var array
     */
    private $typeParserList = [
        RequestEmail::class => "requestEmailParser",
        QuarantineEmail::class => "quarantineEmailParser"
    ];

    /**
     * The request email parser
     * @var null|RequestEmailParser
     */
    private $requestEmailParser = null;

    /**
     * The quarantine email parser
     * @var null|QuarantineEmailParser
     */
    private $quarantineEmailParser = null;

    /**
     * EmailFactory constructor
     */
    public function __construct() {

        // Set attributes
        $this->requestEmailParser = new RequestEmailParser();
        $this->quarantineEmailParser = new QuarantineEmailParser();
    }

    /**
     * Create an "App\Emails\Email" object
     * @param Message|array $message
     * @return RequestEmail|QuarantineEmail
     * @throws ExtendedException
     */
    public function create($message) : Email {

        // Validate message
        $type = null;
        $this->validateMessage($message, $type);

        // Validate type
        $types = array_keys($this->typeParserList);
        $this->validateTType($type, $types);

        // Parse the data to relevant data
        $parser = $this->{$this->typeParserList[$type]};
        $parsedData = $parser->parse($message);
        
        // Return new "App\Emails\Email" object, containing the relevant data
        return new $type($parsedData);
    }

    /**
     * If message is not an instance of "App\Overrides\Webklex\Message" and also not an array, throw
     * exception
     * Else updating $type and if array also $message variable
     * @param $message
     * @param $type
     * @throws ExtendedException
     */
    private function validateMessage(&$message, &$type) : void {

        // "RequestEmail" type
        if ($message instanceof Message)
            $type = $message->getTargetEmailType();

        // "QuarantineEmail" type
        else if (is_array($message)) {
            $type = $message["targetEmailType"];
            $message = $message["quarantineEmailData"];
        }

        // Invalid email type
        else {
            $requiredType = Message::class;
            $givenType = gettype($message);
            throw new ExtendedException("Passed parameter '\$message' must either be an instance of " .
                "'$requiredType' or type array, '$givenType' given.");
        }
    }

    /**
     * If the desired target format of the message is not one of the defined types, throw exception
     * @param $type
     * @param array $types
     * @throws ExtendedException
     */
    private function validateTType($type, array $types) : void {
        if(!in_array($type, $types))
            throw new ExtendedException("Target email type '$type' is invalid.");
    }
}
