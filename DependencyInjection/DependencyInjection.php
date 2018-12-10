<?php

declare(strict_types=1);

namespace namespace Marussia\Components\DependencyInjection;

class DependencyInjection
{
    private $c;

    public function __constrict(Container $container)
    {
        $this->c = $container;
    }
    
    public function getDependencies(string $class_name)
    {
        $dependencies = [];
        
        $reflection = new ReflectionClass($class_name);

        $constructor = $reflection->getConstructor();
        
        if ($constructor !== null) {
        
            foreach ($constructor->getParameters() as $param) {
            
                $class = $param->getClass();
                $dependencies[] = $class->getName();
            }
        }

        return $dependencies;
    }
}
