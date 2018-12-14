<?php

namespace Marussia\Components\DependencyInjection\Exception;

class NotFoundException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct($message);
    }


}
