<?php


namespace App\Models\Requests;


use App\Models\ExtendedModel;

class AmavisServerModel extends ExtendedModel
{
    /**
     * Database table
     * @var string
     */
    protected $table = "amavis_server";

    /**
     * Columns that can be used in mass assignment
     * @var array
     */
    protected $fillable = [
        "name"
    ];
}
