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
use Traversable;

/**
 * This trait provides the functionality necessary for a php-enum implementation.
 *
 * <p>Enumerations (Enums) can be thought of a collection of complex constants with methods attached to them.
 * They represent structures which have ONLY constant property values. An example is an Enum which
 * defines a set of available colors in a chat application. This would be a good candidate for an Enum because:
 *
 * <ul>
 *    <li>The are a predefined and finite set of colors available.
 *    <li>Each color definition has more than one attribute (i.e. redValue, greenValue, blueValue)
 *    <li>Every attribute of the color is a constant value. Runtime logic can not affect their values.
 * </ul>
 *
 *
 * <p>An Enum also has the opportunity to expose instance methods and/or static methods for
 * operating against an Enum value (instance) or the list of available values.  These methods MUST
 * NOT alter the values of properties within the Enum instance because as all properties MUST be
 * constant values.  Expanding on the `ChatColor` example from above, an instance method may be provided
 * which returns the HTML code for each color. A static method may be provided which allows retrieval
 * of a specific Color given an HTML code.
 *
 *
 * <p>An Enum value is retrieved by name with the following syntax:
 *
 * <code>
 *    // EnumClass::VALUE_NAME();
 *    $color = ChatColor.RED();
 * </code>
 *
 * <p>Retrieving an Enum value with a non-existent name will throw a BadEnumNameException:
 *
 * <code>
 *    $color = ChatColor.TABLE(); // throws BadEnumNameException
 * </code>
 *
 * <p>An Enum implementation must follow these rules:
 *
 * <ul>
 *   <li>All Enum properties MUST contain ONLY constant values. The outside world MAY be able to
 *   access the value of any property but MUST NOT be able to modify the value of any property.
 *
 *   <li>Each Enum value must equal itself, regardless of how it is retrieved.
 *
 *   <li>All Enum values SHOULD be declared in the class-level phpDoc using the `method` annotation, where the name
 *   of the method matches the name of the Enum value.
 * <ul>
 *
 * @package crussell52/enum
 */
abstract class Enum implements EnumValue
{
    /**
     * @var EnumCollection[]
     */
    private static $_collections = [];

    /**
     * The name of the Enum value.
     *
     * @var string
     */
    private $_name;

    /**
     * The enum value's position within the definitions where the first defined receives an ordinal of 0.
     *
     * @var int
     */
    private $_ordinal;

    /**
     * Returns the name of the Enum value.
     *
     * <code>
     *   $color = ChatColor::RED();
     *   echo $color->getName(); // outputs "RED"
     * </code>
     *
     * @return string
     */
    public final function getName()
    {
        return $this->_name;
    }

    /**
     * This enum value's position in the collection.
     *
     * @return int
     */
    public final function getOrdinal()
    {
        return $this->_ordinal;
    }

    /**
     * Enum values should never be directly instantiated, so this constructor is marked as
     * final/private.
     *
     * @param string $name    The name of the desired Enum value. This must be a valid Enum name as
     *                        defined within self::$_definitions.  It is also possible to retrieve a list
     *                        of available Enum names with the static getNames() method.
     *
     * @param int    $ordinal The enum value's position within the definitions where the first defined enum value
     *                        receives an ordinal value of 0.
     *
     * @throws BadEnumNameException
     */
    private final function __construct($name, $ordinal, $definition)
    {

        $this->_name = $name;
        $this->_ordinal = $ordinal;
        $this->_populate($definition);

        self::_getEnumCollection()->cacheEnumValue($this);
    }

    /**
     * This method initializes the data which defines what Enum names are available and the values for each.
     *
     * <p>This method will only be called once for any given Enum implementation. Implementations should never
     * need to call this method directly.
     *
     * <p>Enum implementations MUST override this method to return an array containing the details of each
     * Enum value. Each key of the returned array MUST define the name of an Enum value and each value of the returned
     * array MUST be an array defining the Enum value's attributes in a way which can be interpreted by the
     * implementation's _populate() method.
     *
     * <p>For example:
     *
     * <code>
     *   class ChatColor
     *   {
     *       use Crussell52\Enum\TEnum;
     *
     *       ...
     *       protected static function _initializeDefinitions()
     *       {
     *           // 'NAME' => [red, green, blue]
     *           return [
     *
     *               'RED'    => [255, 0, 0],
     *               'YELLOW' => [255, 255, 0]
     *           ];
     *       }
     *
     *       ...
     *       protected function _populate(array $definition)
     *       {
     *           $this->_red   = $definition[0];
     *           $this->_green = $definition[1];
     *           $this->_blue  = $definition[2];
     *       }
     *   }
     * </code>
     */
    protected static function _initializeDefinitions()
    {
        return [];
    }

    /**
     * Internal method for initializing the Enum implementation.
     *
     * @return void
     */
    private static function _initialize()
    {
        // If we already have a collection for this Enum implementation, then we have already initialized.
        if (isset(self::$_collections[static::class])) {
            return;
        }

        self::$_collections[static::class] = new EnumCollection(static::_initializeDefinitions());
    }

    /**
     * Allows the Enum value to be coerced into a string by returning its name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_name;
    }

    /**
     * Magic Method implementation to provide an Enum value by invoking its name as a class method.
     *
     * <code>
     *     $color = ChatColor.RED(); // __callStatic("RED", []);
     * </code>
     *
     * @param string $name    The name of the Enum value.
     * @param array  $ignored The magic method receives arguments passed into the original method call, but this
     *                        implementation ignores them.
     *
     * @return static
     */
    public static final function __callStatic($name, $ignored)
    {
        return self::findByName($name);
    }

    /**
     * Retrieves (or creates) the enum value identified by the given name.
     *
     * @param string $name The name of the enum. The given value will be coerced into a string.
     *
     * @return static
     */
    public final function findByName($name)
    {
        // Ask the collection for the enum value.
        $name = (string)$name;
        $enumCollection = self::_getEnumCollection();
        $target = $enumCollection->getByName($name);

        // See if we received an instance of the invoked Enum implementation.
        if ($target instanceof static) {
            // We already have the enum value, just return it.
            return $target;
        }

        // We didn't get an Enum value back, so we can assume that we received a definition from the collection, since
        // we trust our self to follow our own rules...

        // Run the constructor and return the result.
        return new static($name, $enumCollection->getOrdinal($name), $target);
    }

    /**
     * This method receives in a single Enum value definition and populates the instance using that definition.
     *
     * <code>
     *   class ChatColor
     *   {
     *       use Crussell52\Enum\TEnum;
     *
     *       ...
     *       protected static function _initializeDefinitions()
     *       {
     *           // 'NAME' => [red, green, blue]
     *           return = [
     *
     *               'RED'    => [255, 0, 0],
     *               'YELLOW' => [255, 255, 0]
     *           ];
     *       }
     *
     *       ...
     *       protected function _populate(array $definition)
     *       {
     *           $this->_red   = $definition[0];
     *           $this->_green = $definition[1];
     *           $this->_blue  = $definition[2];
     *       }
     *   }
     * </code>
     *
     * @param array $definition The definition of the enum value being populated.
     *
     * @return void
     */
    abstract protected function _populate(array $definition);

    /**
     * This method provides all available names
     *
     * @return array
     */
    public final static function getNames()
    {
        return self::_getEnumCollection()->getNames();
    }

    private static function _getEnumCollection()
    {
        self::_initialize();
        return self::$_collections[static::class];
    }

    /**
     * This method provides a Traversable containing all available enum values.
     *
     * <code>
     *    // Output each available color and its html code.
     *    foreach (ChatColors.getValues() as $color)
     *    {
     *       echo $color.getName() . ": " . $color.getHTMLCode() . "<br/>";
     *    }
     * </code>
     *
     * @return Traversable
     */
    public final static function getValues()
    {
        // Ensure that definitions have been initialized.
        $enumCollection = self::_getEnumCollection();

        // Loop over each available definition and return the instance.
        foreach ($enumCollection->getNames() as $name)
        {
            yield self::findByName($name);;
        }
    }

    /**
     * Finds an enum value using the given ordinal.
     *
     * @param int $ordinal The ordinal value to look up. This value will be coerced into an integer.
     *
     * @throws OrdinalOutOfRangeException This exception is thrown if no enum value exists under the given ordinal.
     *
     * @return static
     */
    public final static function findByOrdinal($ordinal)
    {
        // Ask the collection for the enum value.
        $ordinal = (int)$ordinal;
        $enumCollection = self::_getEnumCollection();
        $target = $enumCollection->getByOrdinal($ordinal);

        // See if we received an instance of the invoked Enum implementation.
        if ($target instanceof static) {
            // We already have the enum value, just return it.
            return $target;
        }

        // We didn't get an Enum value back, so we can assume that we received a definition from the collection, since
        // we trust our self to follow our own rules...

        // Run the constructor and return the result.
        return new static($enumCollection->getName($ordinal), $ordinal, $target);
    }
}
