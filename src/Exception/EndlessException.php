<?php

namespace Marussia\DependencyInjection\Exception;

class EndlessException extends \Exception
{

    public function __construct($class_name, $dep_class_name)
    {
        $message = 'Класс ' . $class_name . ' имеет циклическую зависимость от класса ' . $dep_class_name;
    
        parent::__construct($message);
    }


} 
