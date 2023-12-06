<?php


namespace App\Collections;


use App\Exceptions\ExtendedException;
use App\Utils\ArrayUtil;

abstract class Collection
{
    /**
     * The items contained in the collection
     * @var array
     */
    protected $items = [];

    /**
     * The required item type
     * @var null
     */
    protected $validTypes = null;

    /**
     * Collection constructor
     * @param array $items
     * @param string[] $validTypes
     * @throws ExtendedException
     */
    public function __construct(array $items = [], array $validTypes = []) {

        // Set required type
        $this->validTypes = $validTypes;

        // Validate "$items"
        $this->validateItems($items);

        // Set items
        $this->items = $items;
    }

    /**
     * Validate the type of added items
     * @param $items
     * @throws ExtendedException
     */
    protected function validateItems($items) : void {

        // If no required type set, return
        if (!sizeof($this->validTypes) <= 0)
            return;

        // Convert "$item" to array, if not an array passed
        if (is_array($items)) {
            $exceptionMessageBody = "must contain only items of type";
        } else {
            $items = [ $items ];
            $exceptionMessageBody = "must be an instance of";
        }

        // If any item of "$requestMessages" is not an instance of one of the specified types, throw exception
        foreach ($items as $item) {
            $matchingValidTypeFound = false;

            // Check item for each type
            foreach ($this->validTypes as $validType) {
                if ($item instanceof $validType)
                    $matchingValidTypeFound = true;
            }

            // If no matching valid type found, throw exception
            if (!$matchingValidTypeFound) {
                $providedType = gettype($item);
                $validTypesString = ArrayUtil::toString($this->validTypes);
                throw new ExtendedException("Passed items $exceptionMessageBody " .
                    "'$validTypesString', '$providedType' provided.");
            }
        }
    }

    /**
     * Add an item to the collection
     * @param mixed $item
     * @param string|null $key
     * @return $this
     * @throws ExtendedException
     */
    public function add($item, string $key = null) : self {

        // Validate the item to add
        $this->validateItems($item);

        // Att the item
        if (is_string($key))
            $this->items[$key] = $item;
        else
            $this->items[] = $item;

        // Return instance
        return $this;
    }

    /**
     * Retrieve all items from the collection
     * @return array
     */
    public function all() : array {
        return $this->items;
    }

    /**
     * Get item from key
     * @param string $key
     * @return mixed
     */
    public function get(string $key) {
        return $this->items[$key];
    }
}
