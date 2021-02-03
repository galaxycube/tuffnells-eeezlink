<?php
namespace Tuffnells\Exceptions;


use Throwable;

class EndpointError extends \Exception
{
    /**
     * EndpointError constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Tuffnells Eeezlink threw an error - ' . $message, $code, $previous);
    }
}