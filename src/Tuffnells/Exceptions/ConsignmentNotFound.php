<?php
namespace Tuffnells\Exceptions;

use \Throwable;

class ConsignmentNotFound extends \Exception
{
    /**
     * EndpointError constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        parent::__construct('Consignment Not Found - ' . $message, $code, $previous);
    }
}