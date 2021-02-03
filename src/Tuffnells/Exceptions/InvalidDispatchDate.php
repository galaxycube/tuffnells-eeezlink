<?php
namespace Tuffnells\Exceptions;

class InvalidDispatchDate extends \Exception
{
    /** The error message */
    protected $message = 'Invalid Delivery Date, Must be future dated';
}