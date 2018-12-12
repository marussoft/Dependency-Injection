<?php 

declare(strict_types=1);

namespace Marussia\Components\DependencyInjection;

class Container
{
    private $reflections;
    
    private $dependencies;
    
    private $definations;
    
    private $params;
    
    public function get($class_name)
    {
        if (isset($this->definations[$class_name])) {
            return $this->definations[$class_name];
        }
    }
    
    private function instance($class_name, $params)
    {
        $this->params[$class_name] = $params;
        
        $this->buildDependencies($class_name);
        
        $this->instanceRecursive();
        
        return $this->get[$class_name];
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
        end($this->dependencies);
        
        while ($deps !== false) {
        
            $class = key($this->dependencies);
            
            $deps = current($this->dependencies);
            
            $this->instanceDependencies($class, $deps);
            
            prev($this->dependencies);
        }
        
        
    }
    
    private function instanceDependencies($class, $deps)
    {
        $dependencies = [];
    
        foreach ($deps as $dep) {
            
            if (isset($this->dependencies[$dep])) {
                if (isset($this->definations[$dep])) {
                    $dependencies[] = $this->definations[$dep];
                } else {
                    $this->instanceDependencies($dep, $this->definations[$dep]);
                }
                
            } else {
                $dependencies[] = $this->reflections[$dep]->newInstanceWithoutConstructor()
            }
        }
        
        $instance = $this->reflections[$class]->newInstanceArgs($dependencies);
    }
    
    private function mergeParams($class_name, $params)
    {
        if (isset($this->params[$class_name])) {
            return array_merge($this->params[$class_name], $params);
        }
    }
    
}
