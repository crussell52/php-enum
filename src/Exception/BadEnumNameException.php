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
 * This exception is appropriate for cases when an Enum value is requested by a name which does not match any defined
 * Enum value.
 *
 * @package CRussell52\Enum\Exception
 */
class BadEnumNameException extends EnumNotFoundException
{
    /**
     * The name which failed to resolve to an Enum value.
     *
     * @var string
     */
    private $_badName;

    /**
     * A list of available Enum value names.
     *
     * @var array
     */
    private $_availableNames;

    /**
     * @param string          $badName         The name which failed to resolve to an Enum value.
     * @param array           $availableNames  A list of all available Enum value names.
     * @param string          $message         A custom message for the exception. If omitted, then a default message
     *                                         which gives the bad name and all available names will be used.
     * @param int             $code            An optional code which further qualifies the exception case.
     * @param \Exception|null $previous        The exception which caused this exception.
     */
    public function __construct($badName, array $availableNames, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->_badName = $badName;
        $this->_availableNames = $availableNames;

        if ((string)$message === "") {
            $message = "No Enum value available under that badName ($badName).";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Provides a list of all available Enum value names.
     *
     * @return array
     */
    public function getAvailableNames()
    {
        return $this->_availableNames;
    }

    /**
     * Provides the name which failed to resolve to an Enum name.
     *
     * @return string
     */
    public function getBadName()
    {
        return $this->_badName;
    }
}
