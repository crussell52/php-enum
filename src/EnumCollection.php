<?php
/**
 * php-enum
 *
 * @author    Chris Russell (crussell52@gmail.com)
 * @copyright 2014 Chris Russell (https://www.github.com/crussell52/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://www.github.com/crussell52/php-enum
 */

namespace CRussell52\Enum;


use CRussell52\Enum\Exception\BadEnumNameException;
use CRussell52\Enum\Exception\OrdinalOutOfRangeException;

class EnumCollection
{
    /**
     * ordinal => enumName
     *
     * @var array
     */
    private $_ordinalMap;

    /**
     * enumName => ordinal
     *
     * @var array
     */
    private $_nameMap;

    /**
     * local cache of previously delivered enums.
     *
     * name => Enum
     *
     * @var array
     */
    private $_enumValues = [];

    /**
     * Definitions of enums which are not yet cached.
     *
     * @var array
     */
    private $_enumDefinitions = [];

    public function __construct($definitions)
    {
        $this->_ordinalMap = array_keys($definitions);
        $this->_nameMap = array_flip($this->_ordinalMap);
        $this->_enumDefinitions = $definitions;
    }

    public function getNames()
    {
        return $this->_ordinalMap;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getByName($name)
    {
        if (!isset($this->_nameMap[$name])) {
            throw new BadEnumNameException($name, $this->_ordinalMap);
        }

        return isset($this->_enumValues[$name]) ? $this->_enumValues[$name] : $this->_enumDefinitions[$name];
    }

    public function getByOrdinal($ordinal)
    {
        $name = $this->getName($ordinal);
        return isset($this->_enumValues[$name]) ? $this->_enumValues[$name] : $this->_enumDefinitions[$name];
    }

    public function getName($ordinal)
    {
        $ordinal = (int)$ordinal;
        if (!isset($this->_ordinalMap[$ordinal])) {
            throw new OrdinalOutOfRangeException($ordinal, count($this->_ordinalMap));
        }

        return $this->_ordinalMap[$ordinal];
    }

    public function getOrdinal($name)
    {
        if (!isset($this->_nameMap[$name])) {
            throw new BadEnumNameException($name, $this->_ordinalMap);
        }

        return $this->_nameMap[$name];
    }

    public function cacheEnumValue(EnumValue $enumValue)
    {
        // Extract the name.
        $name = $enumValue->getName();

        // Since we are caching the instance, we don't need the definition any more.
        unset($this->_enumDefinitions[$name]);

        // Add the instance to the cache.
        $this->_enumValues[$name] = $enumValue;
    }
}

