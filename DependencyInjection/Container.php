<?php 

declare(strict_types=1);

namespace namespace Marussia\Components\DependencyInjection;

class Container
{
    private $c;
    private $di;

    public function __constrict()
    {
        $this->di = new DependencyInjection;
    }
}
