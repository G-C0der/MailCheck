<?php


namespace App\Collections;


use App\Api\Connections\ImapConnection;
use App\Api\Connections\Ssh\SftpConnection;
use App\Api\Connections\Ssh\SshConnection;
use App\Exceptions\ExtendedException;

class ConnectionCollection extends Collection
{
    private const VALID_TYPES = [
        ImapConnection::class,
        SshConnection::class,
        SftpConnection::class
    ];

    /**
     * ConnectionCollection constructor
     * @param array $items
     * @throws ExtendedException
     */
    public function __construct(array $items = []) {

        // Parent construction
        parent::__construct($items, self::VALID_TYPES);
    }

    /**
     * Connect all inactive SFTP connection
     */
    public function connectAll() : void {
        foreach ($this->items as $item) {
            $item->establish();
        }
    }

    /**
     * Disconnect all active SFTP connections
     */
    public function disconnectAll() : void {
        foreach ($this->items as $item) {
            $item->terminate();
        }
    }
}
