<?php

namespace Marussia\Components\DependencyInjection\Exception;

class NotFoundException extends \Exception
{

    public function __construct($class_name)
    {
        $message = 'Объект класса ' . $class_name . ' не зарегистрирован.';
    
        parent::__construct($message);
    }


}
