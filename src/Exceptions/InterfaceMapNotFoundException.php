<?php

namespace Marussia\DependencyInjection\Exceptions;

class InterfaceMapNotFoundException extends \Exception
{
    public function __construct(string $interfaceName)
    {
        $message = 'Interface map not found for ' . $interfaceName;
    
        parent::__construct($message);
    }
} 
 
