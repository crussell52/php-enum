<?php
/**
 * php-enum
 *
 * @author    Chris Russell (crussell52@gmail.com)
 * @copyright 2014 Chris Russell (https://www.github.com/crussell52/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://www.github.com/crussell52/php-enum
 */
namespace CRussell52\Enum\Exception;


/**
 * Exceptions of this type are appropriate for any case where an Enum value can not be found using specific criteria.
 *
 * <code>
 *   ChatColor::findByHexCode();
 * </code>
 *
 * @package CRussell52\Enum\Exception
 */
class EnumNotFoundException extends \RuntimeException
{
}