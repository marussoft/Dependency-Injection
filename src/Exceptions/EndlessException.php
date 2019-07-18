<?php

namespace Marussia\DependencyInjection\Exceptions;

class EndlessException extends \Exception
{

    public function __construct($className, $depClassName)
    {
        $message = 'The class ' . $className . ' has a cyclic dependence on the class ' . $depClassName;
    
        parent::__construct($message);
    }


} 
