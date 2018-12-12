<?php 

declare(strict_types=1);

namespace Marussia\Components\DependencyInjection;

class Container
{
    private $reflections;
    
    private $dependencies;
    
    private $definations;
    
    private $params;
    
    private $trees;
    
    public function get($class_name)
    {
        if (isset($this->definations[$class_name])) {
            return $this->definations[$class_name];
        }
    }
    
    public function instance($class_name, $params = [])
    {
        $this->params = $params;
        
        $this->buildDependencies($class_name);
        
        $this->instanceRecursive();
        
        $this->trees[$class_name] = $this->dependencies;
        
        $this->dependencies = [];
        
        return $this->get($class_name);
    }
    
    private function buildDependencies(string $class_name)
    {
        if (isset($this->reflections[$class_name])) {
            $reflection = $this->reflections[$class_name];
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
        $deps = end($this->dependencies);
        
        while ($deps !== false) {
        
            $class = key($this->dependencies);
            
            $deps = current($this->dependencies);

            if (prev($this->dependencies) === false) {
                $this->instanceRequire($class, $deps);
                break;
            }
            
            $this->instanceDependencies($class, $deps);
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
                $this->definations[$dep] = $this->reflections[$dep]->newInstance();
            }
            $dependencies[] = $this->definations[$dep];
        }
        
        $this->definations[$class] = $this->reflections[$class]->newInstanceArgs($dependencies);
    }
    
    private function instanceRequire($class, $deps)
    {
        $dependencies = [];
        
        foreach ($deps as $dep) {
            $dependencies[] = $this->definations[$dep];
        }
        
        if (!empty($this->params)) {
            $dependencies = array_merge($dependencies, $this->params);
        }
        
        $this->definations[$class] = $this->reflections[$class]->newInstanceArgs($dependencies);
    }
    
}
