<?php 

declare(strict_types=1);

namespace Marussia\Components\DependencyInjection;

class Container
{
    private $reflections;
    
    private $dependencies;
    
    private $instance;
    
    public function get($class_name)
    {
        if (isset($this->instance[$class_name])) {
            return $this->instance[$class_name];
        }
        
        $this->buildDependencies($class_name, $this);
        
        $this->instanceRecursive();
        
        $this->instance($class_name);
        
        return $this->instance[$class_name];
    }
    
    private function instance()
    {
    
    }
    
    private function buildDependencies(string $class_name)
    {
        if (isset($this->reflections[$class_name])) {
            $reflection = $this->reflections[$class_name]ж
        } else {
            $reflection = new \ReflectionClass($class_name);
            $this->reflections[$class_name] = $reflection;
        }

        // Получаем конструктор
        $constructor = $reflection->getConstructor();
        
        if ($constructor !== null) {
        
            // Проходим по параметрам конструктора
            foreach ($constructor->getParameters() as $param) {
            
                // Получаем класс из подсказки типа
                $class = $param->getClass();
                
                // Если в параметрах есть зависимость то получаем её
                if (null !== $class) {
                    $dep_class_name = $class->getName();
                    $this->dependencies[$class_name][] = $dep_class_name;
                    $this->buildDependencies($dep_class_name);
                }
            }
        }
    }
    
    private function instanceRecursive()
    {
        $reflection->newInstanceArgs($dependencies);
    }
    
}
