<?php


namespace App\Utils;


use App\Exceptions\ExtendedException;

abstract class ArrayUtil
{
    /**
     * Check if array is associative
     * @param array $array
     * @return bool
     */
    public static final function isAssociative(array $array) : bool {

        // Get array keys
        $arrayKeys = array_keys($array);

        // Get a list which contains a boolean value for every array key which determines if the key is as string
        $keyIsStringList = array_filter($arrayKeys, 'is_string');

        // Return if array only has string keys
        return count($keyIsStringList) > 0;
    }

    /**
     * Convert array into string array representation
     * Example of converted array: "[ 'ab', 'bc', 'cd' ]"
     * @param array $array
     * @return string
     */
    public static final function toString(array $array) : string {
        return "[ " . implode(", ", $array) . " ]";
    }

    /**
     * Validate presence and type of each attribute in an array
     * @param array $attributes
     * @param array $requiredAttributeKeys
     * @param array $defaultRequiredTypes
     * @throws ExtendedException
     */
    public static final function validateAttributes(array $attributes, array $requiredAttributeKeys,
                                        array $defaultRequiredTypes = [ "string" ]) : void {

        // If not all required attributes present, throw exception
        self::validateAttributePresences($attributes, $requiredAttributeKeys);

        // If not all attributes of the required type (if no required type specified, the specified default type is
        // used), throw exception
        self::validateAttributeTypes($attributes, $requiredAttributeKeys, $defaultRequiredTypes);
    }

    /**
     * Validate that the attribute keys of "$attributes" match the array of attribute keys "$requiredAttributes", else
     * throw exception
     * @param array $attributes
     * @param array $requiredAttributeKeys
     * @throws ExtendedException
     */
    public static final function validateAttributePresences(array $attributes, array $requiredAttributeKeys) : void {
        $passedAttributeKeys = array_keys($attributes);
        $requiredAttributeValues = array_values($requiredAttributeKeys);
        $differences = array_diff($requiredAttributeValues, $passedAttributeKeys);
        if (sizeof($differences) > 0) {
            $passedAttributeKeysString = ArrayUtil::toString($passedAttributeKeys);
            $requiredAttributesString = ArrayUtil::toString($requiredAttributeKeys);
            throw new ExtendedException("The passed attributes '$passedAttributeKeysString' do not match the" .
                " required attributes '$requiredAttributesString'.");
        }
    }

    /**
     * Validates that all attributes are of the required type, else throw exception
     * Example array to validate: [
     *      "someString",
     *      "int" => 524,
     *      "int,bool" => true
     * ]
     * If no type ist specified like in the first item of the example array, the provided default types
     * "$defaultRequiredTypes" will be used
     * @param array $attributes
     * @param array $requiredAttributeKeys
     * @param array $defaultRequiredTypes
     * @throws ExtendedException
     */
    public static final function validateAttributeTypes(array $attributes, array $requiredAttributeKeys,
                                                        array $defaultRequiredTypes = [ "string" ]) : void {
        foreach ($requiredAttributeKeys as $key => $requiredAttributeKey) {

            // Set required type, depending on whether a required type is specified
            $requiredTypes = $defaultRequiredTypes;
            $typeType = "default";
            if (!is_numeric($key)) {
                $requiredTypes = explode(",", $key);
                $typeType = "required";
            }

            // If attribute not one of the required types, throw exception
            $passedAttribute = $attributes[$requiredAttributeKey];
            $passedType = gettype($passedAttribute);
            if (!in_array($passedType, $requiredTypes)) {
                $requiredTypesString = ArrayUtil::toString($requiredTypes);
                throw new ExtendedException("All passed attributes must be one of following $typeType types " .
                    "'$requiredTypesString', type '$passedType' provided");
            }
        }
    }

    /**
     * Reindex the first dimension of the array
     * @param array $array
     * @return array
     */
    public static final function reindex(array &$array) : array {
        $array = array_values($array);
        return $array;
    }
}
