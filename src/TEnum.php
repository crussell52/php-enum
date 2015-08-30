<?php
/**
 * php-enum
 *
 * @author    Chris Russell (crussell52@gmail.com)
 * @copyright 2014 Chris Russell (https://www.github.com/crussell52/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://www.github.com/crussell52/php-enum
 */
namespace CRusell52\Enum;

use CRusell52\Enum\Exception\BadEnumNameException;
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
trait TEnum
{
    /**
     * An associative array where each key represents an Enum name name in the Enum implementation
     * and each value is an array of the values which represent the attribute values of the related Enum value.
     *
     * <p>The values of this array MUST be populated during execution of _initializeDefinitions().
     *
     * @see getName()
     * @see _populate()
     * @see _initializeDefinitions()
     *
     * @var array[]
     */
    private static $_definitions;

    /**
     * A memory-resident cache of previously created Enum instances, keyed by name.
     *
     * <p>Because an Enum instance represents constant values there is never a need for more than one
     * instance.
     *
     * @var array
     */
    private static $_enums = [];

    /**
     * The name of the Enum value.
     *
     * @var string
     */
    private $_name;

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
     * Enum values should never be directly instantiated, so this constructor is marked as
     * final/private.
     *
     * @param string $name The name of the desired Enum value. This must be a valid Enum name as
     *                     defined within self::$_definitions.  It is also possible to retrieve a list
     *                     of available Enum names with the static getNames() method.
     *
     * @throws BadEnumNameException
     */
    private final function __construct($name)
    {
        // Ensure that definitions have been initialized.
        self::_initializeDefinitions();

        // If the name is not valid, then throw an exception.
        if (!isset(self::$_definitions[$name]))
        {
            throw new BadEnumNameException($name, self::getNames());
        }

        $this->_name = $name;
        $this->_populate(self::$_definitions[$name]);
    }

    /**
     * This method initializes the data which defines what Enum names are available and the values for each.
     *
     * <p>Implementations of this method MUST override this method to set the value of self::$_definitions.
     *
     * <p>This method will only be called once for any given Enum implementation. Implementations should never
     * need to call this method directly.
     *
     * <p>Enum implementations MUST set the value of self::$_definitions within this method to define the details of each
     * Enum value. Each key defines the name of an Enum value and each value MUST be an array defining the Enum value's
     * attributes in a way which can be interpreted by the implementation's _populate() method.
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
     *           self::$_definitions = [
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
        self::$_definitions = [];
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
        $name = (string)$name;
        if (isset(self::$_enums[$name]))
        {
            return self::$_enums[$name];
        }

        // Make sure definitions have been initialized.
        if (!isset(self::$_definitions))
        {
            // Enum values haven't been set yet. This case will only occur once per Enum subclass.
            self::_initializeDefinitions();
        }

        // Run the constructor.  It will fail if the name is invalid.
        return self::$_enums[$name] = new static($name);
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
     *           self::$_definitions = [
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
        // Ensure that definitions have been initialized.
        self::_initializeDefinitions();

        // Return the available names.
        return array_keys(self::$_definitions);
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
        // Make sure values are set.
        if (!isset(self::$_definitions))
        {
            // Enum values haven't been set yet. This case will only occur once per Enum subclass.
            self::_initializeDefinitions();
        }

        // Loop over each available definition and return the instance.
        foreach (self::$_definitions as $name => $junk)
        {
            yield static::$name();
        }
    }
}
