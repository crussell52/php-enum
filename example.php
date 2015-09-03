<?php
/**
 * php-enum
 *
 * This file provides an example implementation of crussell52/enum/TEnum in the form of an Enum
 * implementation which defines available colors. Various operations using the Color enum are then
 * executed.
 *
 * @author    Chris Russell (crussell52@gmail.com)
 * @copyright 2014 Chris Russell (https://www.github.com/crussell52/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://www.github.com/crussell52/php-enum
 */
use CRussell52\Enum\Enum;

require ('./vendor/autoload.php');

/**
 * This class serves as an example of an Enum implementation. Each value represents a color which
 * is available for use.
 *
 * @method static Color RED()
 * @method static Color GREEN()
 * @method static Color BLUE()
 * @method static Color YELLOW()
 * @method static Color WHITE()
 * @method static Color BLACK()
 */
class Color extends Enum
{
  /**
   * The amount of red which is used to create this color (0-255).
   *
   * @var int
   */
  private $_red;

  /**
   * The amount of green which is used to create this color (0-255).
   *
   * @var int
   */
  private $_green;

  /**
   * The amount of blue which is used to create this color (0-255).
   *
   * @var int
   */
  private $_blue;

  /**
   * @inheritDoc
   */
  protected static function _initializeDefinitions()
  {
    // Definition provides red, green, and blue value (in that order)
    return [
        'RED' => [255, 0, 0],
        'GREEN' => [0, 255, 0],
        'BLUE' => [0, 0, 255],
        'YELLOW' => [255, 255, 0],
        'WHITE' => [255, 255, 255],
        'BLACK' => [0, 0, 0],
    ];
  }

  /**
   * @inheritDoc
   */
  protected function _populate(array $args)
  {
    // Definition provides red, green, and blue value (in that order)
    $this->_red = $args[0];
    $this->_green = $args[1];
    $this->_blue = $args[2];
  }

  /**
   * Provides the amount of red used to create this color (0-255).
   *
   * @return int
   */
  public function getRedValue()
  {
    return $this->_red;
  }

  /**
   * Provides the amount of green used to create this color (0-255).
   *
   * @return int
   */
  public function getGreenValue()
  {
    return $this->_green;
  }

  /**
   * Provides the amount of blue used to create this color (0-255).
   *
   * @return int
   */
  public function getBlueValue()
  {
    return $this->_blue;
  }

  /**
   * Provides the html code for this color.
   *
   * @return string
   */
  public function toHtmlCode()
  {
    return '#' . str_pad(dechex($this->_red), 2, '0', STR_PAD_LEFT) . str_pad(dechex($this->_green), 2, '0', STR_PAD_LEFT) . str_pad(dechex($this->_blue), 2, '0', STR_PAD_LEFT);
  }
}

/**
 * Demonstrate type hinting against Enum implementation by saying something nice about the
 * received Color.
 *
 * @param Color $color The color to say something nice about.
 *
 * @return void
 */
function saySomethingNice(Color $color)
{
  switch ($color)
  {
    case Color::RED():
      $something_nice = ' is like the love of a rose petal.';
      break;

    case Color::BLUE():
      $something_nice = ' is like a deep sea on a sunny day.';
      break;

    default:
      $something_nice = ' is a wonderful color.';
      break;
  }

  echo 'The color ' . $color->getName() . $something_nice . "<br />\n";
}

/**
 * Demonstrate looping over available Enum values by listing all available colors names and their
 * html code.
 *
 * @return void
 */
function listHtmlCodes()
{
  foreach (Color::getValues() as $color)
  {
    /** @var Color $color */
    echo $color->getName() . "({$color->getOrdinal()}: " . $color->toHtmlCode() . "<br />\n";
  }
}

/**
 * Demonstrate explicit equality of Enum values with the same name.
 *
 * @param Color $color1 The first color to compare.
 * @param Color $color2 The second color to compare.
 */
function compareColors(Color $color1, Color $color2)
{
  if ($color1 === $color2)
  {
    $comparison_result = ' is ';
  }
  else
  {
    $comparison_result = ' is not ';
  }

  // Leverage built-in __toString() implementation for $color2.
  echo $color1->getName() . $comparison_result . $color2 . "<br />\n";
}

// Run demonstrations.
echo "<h2>Say Nice Stuff</h2>";
saySomethingNice(Color::BLUE());
saySomethingNice(Color::RED());
saySomethingNice(Color::YELLOW());

echo "<h2>List html codes</h2>";
listHtmlCodes();

echo "<h2>Compare colors</h2>";
compareColors(Color::RED(), Color::BLUE());
compareColors(Color::YELLOW(), Color::YELLOW());

$serialCopyColor = unserialize(serialize(Color::YELLOW()));

if ($serialCopyColor === Color::YELLOW()) {
    echo "\nunexected === match on serialized/deserialized value\n";
}
else {
    echo "\n Expected === mismatch on serialized/deserialized value\n";
}

switch ($serialCopyColor) {
    case Color::YELLOW():
        echo "case match!\n";
        break;
    case Color::BLACK():
        echo "case false positive!\n";
        break;
    default:
        echo "case false negative!\n";
        break;
}