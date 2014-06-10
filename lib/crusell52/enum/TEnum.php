<?php
/**
 * php-enum
 *
 * @author    Chris Russell (crussell52@gmail.com)
 * @copyright 2014 Chris Russell (https://www.github.com/crussell52/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://www.github.com/crussell52/php-enum
 */
namespace crusell52\enum;

use ArrayIterator;
use Iterator;

/**
 * This trait provides implementation details which can be used to facilitate a PHP implementation
 * of Enumerations similar to that of Java.
 *
 * <p>Enumerations (Enums) can be thought of as complex constants with methods attached to them.
 * They represent structures which have ONLY constant property values. An example is an Enum which
 * defines available RGB colors. The red, green, and blue value of any given color is always the
 * same.
 *
 * <p>An Enum also has the opportunity to expose instance methods and/or static methods for
 * operating against an Enum value (instance) or the list of available values.  These methods MUST
 * NOT alter the values of properties within the Enum instance because as all properties MUST be
 * constant values.  Expanding on the Color example from above, an instance method may be provided
 * which returns the HTML code for the Color. A static method may be provided which allows retrieval
 * of a specific Color given an HTML code.
 *
 * <p>An Enum value is retrieved with the following syntax:
 *
 * <code>
 *    // EnumClass::VALUE_NAME()
 *    Color.RED();
 * </code>
 *
 * <p>Because each Enum value is an instance of the Enum implementation, type-hinting can be
 * utilized to ensure that a parameter contains a value represented by that Enum implementation.
 *
 * <code>
 *   function reportHtmlCode(Color $color)
 *   {
 *      echo $color->getHtmlCode();
 *   }
 *
 *   ...
 *   reportHtmlCode(Color.RED());  // #ff0000
 *
 * </code>
 *
 * <p>An Enum implementation must follow these rules:
 *
 * <ul>
 *   <li>
 *      All Enum properties MUST contain ONLY constant values. The outside world MAY be able to
 *      access the value of any property but MUST NOT be able to modify the value of any property.
 *   </li>
 *   <li>
 *     Each Enum value must explicitly equal itself, regardless of how it is retrieved. <br/> <br/>
 *
 *     <code>
 *        Color.RED() === Color.RED()
 *        Color.findByHtmlCode("#ff0000") === Color.RED()
 *     <code>
 *   </li>
 *   <li>
 *     All Enum values SHOULD be declared using @method annotation.
 *   </li>
 *
 * @link http://docs.oracle.com/javase/tutorial/java/javaOO/enum.html
 *
 * @package    com
 * @subpackage revelex
 * @subpackage arch
 */
trait TEnum
{
  /**
   * The name of this Enum instance.
   *
   * @var string
   */
  private $_name;

  /**
   * An associative array where each key represents an available name in the Enum implementation
   * and each value is an array of the values which are used to define the Enum instance.
   *
   * <p>The values of this array MUST be populated during execution of _initializeDefinitions().</p>
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
   * instance. Moreover, there CAN NOT be more than one instance because the following assertion must
   * always be true: </p>
   *
   * <code>
   *  Enum::VALUE() === Enum::VALUE()
   * </code>
   *
   * @var array
   */
  private static $_enums = [];

  /**
   * Returns the name of the Enum instance.
   *
   * <code>
   *   echo (string)Color::RED(); // outputs "RED"
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
   * @throws \InvalidArgumentException
   */
  private final function __construct($name)
  {
    // Ensure that definitions have been initialized.
    self::_initializeDefinitions();

    // If the name is not valid, then throw an exception.
    if (!isset(self::$_definitions[$name]))
    {
      throw new \InvalidArgumentException("Unknown Enum name: $name.");
    }

    $this->_name = $name;
    $this->_populate(self::$_definitions[$name]);
  }

  /**
   * This method initializes the data which defines what Enum names are available and the values
   * for each.
   *
   * <p>This method will only be called once for any given Enum subclass. Subclasses should not
   * need to call this method directly.</p>
   *
   * <p>Concrete Enum subclasses must override this method to define subclass Enum members. Each key
   * should represent an Enum name and each value should be an array containing its data. The data
   * array will be passed into the subclass implementation of _populate().</p>
   *
   * For example:</p>
   *
   * <code>
   *   class Color extends Enum
   *   {
   *     ...
   *     protected static function _initializeDefinitions()
   *     {
   *       self::$_definitions = [
   *         RED    => [255, 0, 0],
   *         YELLOW => [255, 255, 0]
   *       ];
   *     }
   *
   *     ...
   *     protected function _populate(array $args)
   *     {
   *       $this->_red   = $args[0];
   *       $this->_green = $args[1];
   *       $this->_blue  = $args[2]
   *     }
   *   }
   * </code>
   */
  protected static function _initializeDefinitions()
  {
    self::$_definitions = [];
  }

  /**
   * {@inheritDoc}
   */
  public function __toString()
  {
    return $this->_name;
  }

  /**
   * {@inheritDoc}
   */
  public static final function __callStatic($name, $args)
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
   * This method receives in a single enum definition and populates the instance using that
   * definition.
   *
   * <code>
   *   class Color extends Enum
   *   {
   *     ...
   *     protected static function _initializeDefinitions()
   *     {
   *       self::$_definitions = [
   *         RED    => [255, 0, 0],
   *         YELLOW => [255, 255, 0]
   *       ];
   *     }
   *
   *     ...
   *     protected function _populate(array $args)
   *     {
   *       $this->_red   = $args[0];
   *       $this->_green = $args[1];
   *       $this->_blue  = $args[2]
   *     }
   *   }
   * </code>
   *
   * @param array $args
   *
   * @return void
   */
  abstract protected function _populate(array $args);

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
   *    foreach (Colors.getValues() as $Color)
   *    {
   *       echo $Color.getName() . ": " . $Color.getHTMLCode() . "<br/>";
   *    }
   * </code>
   *
   * @internal This implementation can be easily updated to use Generators if being executed on
   *           PHP >= 5.5 for an efficiency gain. The related code is commented out below.
   *
   * @return Iterator
   */
  public final static function getValues()
  {
    // Make sure values are set.
    if (!isset(self::$_definitions))
    {
      // Enum values haven't been set yet. This case will only occur once per Enum subclass.
      self::_initializeDefinitions();
    }

//    // PHP 5.5 implementation using Generator.
//    // Loop over each available definition and return the instance.
//    foreach (self::$_definitions as $name => $junk)
//    {
//      yield static::$name();
//    }

    // Build out array of available values.
    $values = [];
    foreach (self::$_definitions as $name => $junk)
    {
      $values[] = static::$name();
    }

    // Return a an iterator containing all available values.
    return new ArrayIterator($values);
  }
}
