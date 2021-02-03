<?php
namespace Tuffnells\Exceptions;

class InvalidPackageQuantity extends \Exception
{
    /** The error message */
    protected $message = 'Invalid Package Quantity must be greater than 1';
}