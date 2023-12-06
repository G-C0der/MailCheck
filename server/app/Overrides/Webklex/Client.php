<?php


namespace App\Overrides\Webklex;


use Webklex\IMAP\Exceptions\MaskNotFoundException;

class Client extends \Webklex\IMAP\Client
{
    /**
     * Client constructor
     * @param array $config
     * @throws MaskNotFoundException
     */
    public function __construct(array $config = []) {

        // Parent construction
        parent::__construct($config);
    }

    /**
     * Get a folder instance by a folder name
     * @param string        $folder_name
     * @param int           $attributes
     * @param null|string   $delimiter
     * @param boolean       $prefix_address
     * @return Folder
     */
    public function getFolder($folder_name, $attributes = 32, $delimiter = null, $prefix_address = true) {

        $delimiter = $delimiter === null ? config('imap.options.delimiter', '/') : $delimiter;

        $folder_name = $prefix_address ? $this->getAddress().$folder_name : $folder_name;

        return new Folder($this, (object) [
            'name'       => $folder_name,
            'attributes' => $attributes,
            'delimiter'  => $delimiter
        ]);
    }
}
