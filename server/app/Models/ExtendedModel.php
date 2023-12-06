<?php


namespace App\Models;


use App\Exceptions\ExtendedException;
use App\Utils\ArrayUtil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class ExtendedModel extends Model
{
    /**
     * The primary key
     * @var string
     */
    protected $primaryKey = "pk_id";

    /**
     * Query specific field
     * @param string $column
     * @param array $queryColumnValuePairs
     * @param bool $firstOccurrence
     * @param string $defaultQuery
     * @return Collection|$this
     * @throws ExtendedException
     */
    public function querySpecific(string $column, array $queryColumnValuePairs, $firstOccurrence = false,
                                  string $defaultQuery = "where") {

        // Find the specific value of the matching database record
        $query = $this->select($column);
        foreach ($queryColumnValuePairs as $queryName => $columnValuePair) {

            // Validate the "$queryColumnValuePair"
            $this->validateColumnValuePair($columnValuePair);

            // Extend the query
            $queryName = is_numeric($queryName) ? $defaultQuery : $queryName;
            $query->{$queryName}($columnValuePair[0], $columnValuePair[1]);
        }

        // Return the result, depending on if only the first occurrence is needed
        if ($firstOccurrence) {
            $value = $query->first();
            return $value ? $value->pluck($column)->toArray()[0] : null;
        }
        return $query->get()->pluck($column);
    }

    /**
     * Validate column value pairs of type array
     * @param array $columnValuePair
     * @throws ExtendedException
     */
    private function validateColumnValuePair(array &$columnValuePair) : void {

        // If not exactly two items ("column" & "value"), throw exception
        $size = sizeof($columnValuePair);
        if (!$size === 2)
            throw new ExtendedException("Column value pair must contain exactly 2 items, the column name " .
                "and the value. $size items provided.");

        // Reindex the column value pair
        ArrayUtil::reindex($columnValuePair);

        // If index "0" ("column") is not string, throw exception
        if (!is_string($columnValuePair[0])) {
            $actualType = gettype($columnValuePair[0]);
            throw new ExtendedException("Column value pairs first item must be the column name, which must " .
                "be of type 'string', '$actualType' provided.");
        }
    }

    /**
     * Query all rows
     * Row order can be specified
     * @param array $orderBy
     * @return Collection
     * @throws ExtendedException
     */
    public function queryAll(array $orderBy = [ 'created_at' => "desc" ]) : Collection {

        // If array not associative throw exception
        if (!ArrayUtil::isAssociative($orderBy))
            throw new ExtendedException("Array passed to \$orderBy parameter must be associative.");

        // Start the query with the model instance
        $query = $this;

        // For each column which the result set should be ordered by
        foreach ($orderBy as $column => $order) {

            // If invalid order throw exception
            if (strtolower($order) !== "asc" && strtolower($order) !== "desc")
                throw new ExtendedException("Order type '$order' invalid.");

            // If column exists in table
            if (!Schema::hasColumn($this->table, $column))
                continue;

            // Order the result set
            $query = $query->orderBy($column, $order);
        }

        // Get and return the result set
        return $query->get();
    }

    /**
     * Insert a row
     * @param array $data
     * @return int
     */
    public function insertRow(array $data) : int {
        $model = $this->create($data);
        return (int) $model->{$this->primaryKey};
    }

    /**
     * Check if a valid id
     * @param $id
     * @return bool
     */
    protected final function isValidId($id) : bool {
        return is_numeric($id) && intval($id) > 0;
    }

    /**
     * Check if an entry exists, depending on the provided column value pairs
     * Only "where" queries supported
     * @param array $columnValuePairs
     * @return bool
     */
    public function entryExists(array $columnValuePairs) : bool {

        // Find the id of the matching database record
        $query = $this->select($this->primaryKey);
        foreach ($columnValuePairs as $column => $value)
            $query->where($column, $value);
        $id = $query->first();
        $id = $id ? $id->pluck($this->primaryKey)->toArray()[0] : null;

        // Return if the found / not found id is a valid id
        return $this->isValidId($id);
    }
}
