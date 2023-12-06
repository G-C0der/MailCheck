<?php


namespace App\Processors\Readers;


use App\Collections\AmavisServerCollection;

interface ReaderInterface
{
    /**
     * ReaderInterface constructor
     * @param object $connection
     * @param AmavisServerCollection $amavisServerCollection
     */
    public function __construct(object $connection, AmavisServerCollection $amavisServerCollection);

    /**
     * Get email data
     * @return array
     */
    public function read() : array;
}
