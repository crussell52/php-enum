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


use Closure;
use CRussell52\Enum\Exception\BadEnumNameException;
use CRussell52\Enum\Exception\OrdinalOutOfRangeException;

/**
 * This class is responsible for managing the values of Enum implementations and those values underlying definitions.
 *
 * @package CRussell52\Enum
 */
class EnumValueManager
{
    /**
     * A mapping between ordinal position (key) and EnumValue name (value).
     *
     * @var array
     */
    private $_ordinalMap;

    /**
     * A mapping between EnumValue name (key) and ordinal position (value).
     *
     * @var array
     */
    private $_nameMap;

    /**
     * Local cache of previously delivered EnumValues, keyed by name.
     *
     * name => Enum
     *
     * @var EnumValue[]
     */
    private $_enumValues = [];

    /**
     * Definitions of enums which are not yet cached.
     *
     * @var array
     */
    private $_enumDefinitions = [];

    private $_constructorProxy;

    /**
     * @var Closure
     */
    private static $_constructorProxyTemplate;


    public function __construct($enumClass, $definitions)
    {
        // Make look-up maps from the definitions.
        $this->_ordinalMap = array_keys($definitions);
        $this->_nameMap = array_flip($this->_ordinalMap);

        // Capture the definitions.
        $this->_enumDefinitions = $definitions;

        // See if the template for constructor proxies has been created yet.
        if (!self::$_constructorProxyTemplate) {
            // Create the template for all constructor proxies.
            self::$_constructorProxyTemplate = function($name, $ordinal, $definition) {
                return new static($name, $ordinal, $definition);
            };
        }

        // Create a proxy to the Enum class constructor.
        $this->_constructorProxy = self::$_constructorProxyTemplate->bindTo(null, $enumClass);
    }

    public function getNames()
    {
        return $this->_ordinalMap;
    }

    /**
     * @param $name
     *
     * @return EnumValue
     */
    public function getByName($name)
    {
        // If we already have an instance to deliver, deliver it.
        if (isset($this->_enumValues[$name])) {
            return $this->_enumValues[$name];
        }

        if (!isset($this->_nameMap[$name])) {
            throw new BadEnumNameException($name, $this->getNames());
        }

        $proxy = $this->_constructorProxy;
        return $this->_enumValues[$name] = $proxy($name, $this->_nameMap[$name], $this->_enumDefinitions[$name]);
    }

    public function getByOrdinal($ordinal)
    {
        return $this->getByName($this->getName($ordinal));
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
}

