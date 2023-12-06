<?php


namespace App\Utils;


use App\Exceptions\ExtendedException;

abstract class FileSystemUtil
{
    /**
     * Converts the file size in bytes into the appropriate unit with the proper suffix
     * @param float $bytes
     * @return string
     * @throws ExtendedException
     */
    public static final function formatFileSize(float $bytes) : string {

        // If less than one byte passed throw exception
        if ($bytes < 1)
            throw new ExtendedException("At least '1' byte must be passed to parameter \$bytes. '$bytes' bytes passed");

        // Define result
        $result = null;

        // List of possible units
        $units = [
            [ "suffix" => "TB", "value" => pow(1024, 4) ],
            [ "suffix" => "GB", "value" => pow(1024, 3) ],
            [ "suffix" => "MB", "value" => pow(1024, 2) ],
            [ "suffix" => "KB", "value" => 1024 ],
            [ "suffix" => "B", "value" => 1 ]
        ];

        // Calculate the appropriate unit
        foreach ($units as $unit) {
            if ($bytes >= $unit["value"]) {
                $result = $bytes / $unit["value"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $unit["suffix"];
                break;
            }
        }

        // Return result
        return $result;
    }
}
