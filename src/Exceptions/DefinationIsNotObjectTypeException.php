<?php

namespace Marussia\DependencyInjection\Exceptions;

class DefinationIsNotObjectTypeException extends \Exception
{
    public function __construct($type)
    {
        $message = 'Defination is not object type. Type of ' . $type . ' given.';
    
        parent::__construct($message);
    }
}
