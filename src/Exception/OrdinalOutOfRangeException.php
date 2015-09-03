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


class OrdinalOutOfRangeException extends EnumNotFoundException
{
    /**
     * The ordinal which failed to resolve to an Enum value.
     *
     * @var int
     */
    private $_badOrdinal;

    /**
     * The highest available ordinal.
     *
     * @var int
     */
    private $_maxOrdinal;

    /**
     * @param string          $badOrdinal The ordinal which failed to resolve to an Enum value.
     * @param int             $maxOrdinal The highest available ordinal.
     * @param string          $message    A custom message for the exception. If omitted, then a default message
     *                                    which gives the bad name and all available names will be used.
     * @param int             $code       An optional code which further qualifies the exception case.
     * @param \Exception|null $previous   The exception which caused this exception.
     */
    public function __construct($badOrdinal, $maxOrdinal, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->_badOrdinal = $badOrdinal;
        $this->_maxOrdinal = $maxOrdinal;

        if ((string)$message === "") {
            $message = "Given ordinal ($badOrdinal) is out of range. The maximum ordinal value is $maxOrdinal.";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * The highest available ordinal
     *
     * @return int
     */
    public function getMaxOrdinal()
    {
        return $this->_maxOrdinal;
    }

    /**
     * Provides the ordinal which failed to resolve to an Enum name.
     *
     * @return int
     */
    public function getBadOrdinal()
    {
        return $this->_badOrdinal;
    }
}