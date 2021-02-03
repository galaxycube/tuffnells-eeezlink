<?php
namespace Tuffnells\Exceptions;

use Throwable;

class InvalidConsignment extends \Exception
{
    /**
     * EndpointError constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Invalid Consignment - ' . $message, $code, $previous);
    }
}