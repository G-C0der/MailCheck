<?php


namespace App\Processors\Parsers;


use App\Overrides\Webklex\Message;

interface ParserInterface
{
    /**
     * Parse the email to relevant data
     * @param Message|string $email
     * @return array
     */
    public function parse($email) : array;
}
