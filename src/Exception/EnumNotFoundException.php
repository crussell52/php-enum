<?php
/**
 * php-enum
 *
 * @author    Chris Russell (crussell52@gmail.com)
 * @copyright 2014 Chris Russell (https://www.github.com/crussell52/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://www.github.com/crussell52/php-enum
 */
namespace CRusell52\Enum\Exception;


/**
 * Exceptions of this type are appropriate for cases where an attempt to find an enum value fails. For example, it would
 * be appropriate for the following method:
 *
 * <code>
 *   ChatColor::findByHexCode();
 * </code>
 *
 * @package CRusell52\Enum\Exception
 */
class EnumNotFoundException extends \Exception
{
}