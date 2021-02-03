<?php
namespace Tuffnells\Exceptions;

use Throwable;

class InvalidCache extends \Exception
{
    /**
     * InvalidCache constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Cache Error - ' . $message, $code, $previous);
    }
}