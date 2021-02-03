<?php
namespace Tuffnells\Exceptions;

use Throwable;

class PostcodeNotValid extends \Exception
{
    /**
     * EndpointError constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Invalid Postcode - ' . $message, $code, $previous);
    }
}