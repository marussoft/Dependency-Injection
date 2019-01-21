<?php 

declare(strict_types=1);

namespace Marussia\DependencyInjection;

use Marussia\DependencyInjection\Exception\EndlessException;
use Marussia\DependencyInjection\Exception\NotFoundException;

class Container implements ContainerInterface
{
    private $reflections;
    
    private $dependencies = [];
    
    private $definations;
    
    private $params;
    
    private $trees = [];
    
    private $singleton;

    public function get(string $class_name)
    {
        if (!isset($this->definations[$class_name])) {
            throw new NotFoundException($class_name);
        }
        return $this->definations[$class_name];
    }
    
    public function has(string $class_name) : bool
    {
        return isset($this->definations[$class_name]);
    }
    
    public function instance(string $class_name, array $params = [], bool $singleton = true)
    {
        $this->tmp = [];
        
        $this->dependencies = [];
    
        $this->singleton = $singleton;
    
        $this->params = $params;
        
        $this->prepareDependencies($class_name);
        
        if (count($this->dependencies) === 1) {
            $this->instanceSingleClass($class_name);
            return $this->getDefination($class_name);
        }
        
        if (empty($this->dependencies)) {
            $this->instanceClass($class_name);
            return $this->getDefination($class_name);
        }
        
        $this->iterateDependensies();
        
        $this->trees = array_merge($this->trees, $this->dependencies);

        return $this->getDefination($class_name);
    }
    
    private function prepareDependencies(string $class_name) : void
    {
        if (isset($this->trees[$class_name])) {
            $this->dependencies[$class_name] = $this->trees[$class_name];
            return;
        }
    
        if (!isset($this->reflections[$class_name])) {
            $this->reflections[$class_name] = new \ReflectionClass($class_name);
        }

        // Получаем конструктор
        $constructor = $this->reflections[$class_name]->getConstructor();
        
        if ($constructor !== null) {
            $this->buildDependencies($constructor, $class_name);
        }
    }
    
    // Рекурсивно выстраивает зависимости
    private function buildDependencies(\ReflectionMethod $constructor, string $class_name) : void
    {
        // Проходим по параметрам конструктора
        foreach ($constructor->getParameters() as $param) {
        
            // Получаем класс из подсказки типа
            $class = $param->getClass();
            
            // Если в параметрах есть зависимость то получаем её
            if (null !== $class) {
            
                $dep_class_name = $class->getName();
                
                // Если класс зависит от запрошенного то это циклическая зависимость
                if (isset($this->dependencies[$dep_class_name])) {
                    throw new EndlessException($class_name, $dep_class_name);
                }
                
                $this->dependencies[$class_name][] = $dep_class_name;
                $this->prepareDependencies($dep_class_name);
            }
        }
    }
    
    private function iterateDependensies() : void
    {
        $deps = end($this->dependencies);
        
        while ($deps !== false) {
        
            $class = key($this->dependencies);
            
            $deps = current($this->dependencies);

            if (prev($this->dependencies) === false) {
                $this->instanceSingleClass($class, $deps);
                break;
            }

            $this->instanceRecursive($class, $deps);
        }
    }
    
    // Рекурсивно проходит по зависимостям
    private function instanceRecursive(string $class, array $deps) : void
    {
        $dependencies = [];
    
        foreach ($deps as $dep) {
            
            if (isset($this->dependencies[$dep])) {
            
                if ($this->hasDefination($dep)) {
                    $dependencies[] = $this->getDefination($dep);
                } else {
                    $this->instanceRecursive($dep, $this->getDefination($dep));
                }

            } else {
                $this->setDefination($dep, $this->reflections[$dep]->newInstance());
            }
            
            $dependencies[] = $this->getDefination($dep);
        }
        
        $this->setDefination($class, $this->reflections[$class]->newInstanceArgs($dependencies));
    }
    
    private function instanceSingleClass(string $class) : void
    {
        foreach ($this->dependencies[$class] as $dep) {
            
            if (!$this->hasDefination($dep)) {
                $this->setDefination($dep, $this->reflections[$dep]->newInstance());
            }
        }
        $this->instanceClass($class, $this->dependencies[$class]);
    }
    
    private function instanceClass(string $class, array $deps = []) : void
    {
        $dependencies = [];
        
        foreach ($deps as $dep) {
            $dependencies[] = $this->getDefination($dep);
        }
        
        if (!empty($this->params)) {
            $dependencies = array_merge($dependencies, $this->params);
        }
        $this->setDefination($class, $this->reflections[$class]->newInstanceArgs($dependencies));
    }
    
    private function setDefination(string $class_name, $defination) : void
    {
        if ($this->singleton) {
            $this->definations[$class_name] = $defination;
        } else {
            $this->tmp[$class_name] = $defination;
        }
    }
    
    private function getDefination(string $class_name)
    {
        if ($this->singleton) {
            return $this->definations[$class_name];
        }
        return $this->tmp[$class_name];
    }
    
    private function hasDefination(string $class_name) : bool
    {
        if ($this->singleton) {
            return isset($this->definations[$class_name]);
        }
        return isset($this->tmp[$class_name]);
    }
}
