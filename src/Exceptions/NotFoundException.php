<?php

namespace Marussia\DependencyInjection\Exceptions;

class NotFoundException extends \Exception implements \Psr\Container\NotFoundExceptionInterface
{
    public function __construct($className)
    {
        $message = 'Instance of ' . $className . ' not found.';
    
        parent::__construct($message);
    }
}
