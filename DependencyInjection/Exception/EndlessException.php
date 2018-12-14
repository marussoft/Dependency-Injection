<?php

namespace Marussia\Components\DependencyInjection\Exception;

class EndlessException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct($message);
    }


} 
