<?php

namespace Marussia\DependencyInjection\Exceptions;

class DefinitionIsNotObjectTypeException extends \Exception implements \Psr\Container\ContainerExceptionInterface
{
    public function __construct($type)
    {
        $message = 'Defination is not object type. Type of ' . $type . ' given.';
    
        parent::__construct($message);
    }
}
