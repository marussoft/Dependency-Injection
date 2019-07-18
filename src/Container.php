<?php 

declare(strict_types=1);

namespace Marussia\DependencyInjection;

use Marussia\DependencyInjection\Exceptions\EndlessException;
use Marussia\DependencyInjection\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    // Массив рефлексий
    private $reflections;
    
    // Массив дерева зависимостей
    private $dependencies = [];
    
    // Массив объектов
    private $definations;
    
    // Массив параметров
    private $params;
    
    // Сохраненные деревья зависимостей
    private $trees = [];
    
    // Флаг синглтона
    private $singleton;

    public function get(string $className)
    {
        if (!isset($this->definations[$className])) {
            throw new NotFoundException($className);
        }
        return $this->definations[$className];
    }
    
    public function has(string $className) : bool
    {
        return isset($this->definations[$className]);
    }
    
    // Создает инстанс переданного класса
    public function instance(string $className, array $params = [], bool $singleton = true)
    {
        $this->tmp = [];
        
        $this->dependencies = [];
    
        $this->singleton = $singleton;
    
        $this->params = $params;
        
        $this->prepareDependencies($className);
        
        if (count($this->dependencies) === 1) {
            $this->instanceSingleClass($className);
            return $this->getDefination($className);
        }
        
        if (empty($this->dependencies)) {
            $this->instanceClass($className);
            return $this->getDefination($className);
        }
        
        $this->iterateDependensies();
        
        $this->trees = array_merge($this->trees, $this->dependencies);

        return $this->getDefination($className);
    }
    
    public function getClassMap(string $className) : array
    {
        if (!array_key_exists($className, $this->trees)) {
            $this->instance($className);
        }
        return $this->trees[$className];
    }
    
    public function setClassMap(array $classMap) : void
    {
        $this->trees = array_merge($this->trees, $classMap);
    }
    
    // Подготавливает зависимости к рекурсивному инстанцированию 
    private function prepareDependencies(string $className) : void
    {
        // Проверяем наличие ранее созданного дерева зависимостей для класса
        if (isset($this->trees[$className])) {
            $this->dependencies[$className] = $this->trees[$className];
            return;
        }
    
        // Проверяем наличие ранее созданых рефлексий
        if (!isset($this->reflections[$className])) {
            $this->reflections[$className] = new \ReflectionClass($className);
        }

        // Получаем конструктор
        $constructor = $this->reflections[$className]->getConstructor();
        
        if ($constructor !== null) {
            $this->buildDependencies($constructor, $className);
        }
    }
    
    // Рекурсивно выстраивает зависимости
    private function buildDependencies(\ReflectionMethod $constructor, string $className) : void
    {
        // Проходим по параметрам конструктора
        foreach ($constructor->getParameters() as $param) {
        
            // Получаем класс из подсказки типа
            $class = $param->getClass();
            
            // Если в параметрах есть зависимость то получаем её
            if (null !== $class) {
            
                $depClassName = $class->getName();
                
                // Если класс зависит от запрошенного то это циклическая зависимость
                if (isset($this->dependencies[$depClassName][$className])) {
                    throw new EndlessException($className, $depClassName);
                }
                
                $this->dependencies[$className][$depClassName] = $depClassName;
                $this->prepareDependencies($depClassName);
            }
        }
    }
    
    // Проходит по дереву зависимостей
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

            if (empty($deps)) {
                $this->instanceClass($class);
                continue;
            }
            
            $this->instanceRecursive($class, $deps);
        }
    }
    
    // Рекурсивно инстанцирует зависимости
    private function instanceRecursive(string $class, array $deps = []) : void
    {
        $dependencies = [];
    
        foreach ($deps as $dep) {
            
            if (isset($this->dependencies[$dep])) {
            
                if ($this->hasDefination($dep)) {
                    $dependencies[] = $this->getDefination($dep);
                } elseif ($this->getDefination($dep) !== null) {
                    $this->instanceRecursive($dep, $this->getDefination($dep));
                } else {
                    $this->instanceSingleClass($dep);
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
    
    private function setDefination(string $className, $defination) : void
    {
        if ($this->singleton) {
            $this->definations[$className] = $defination;
        } else {
            $this->tmp[$className] = $defination;
        }
    }
    
    private function getDefination(string $className)
    {
        if ($this->singleton) {
            return $this->definations[$className];
        }
        return $this->tmp[$className];
    }
    
    private function hasDefination(string $className) : bool
    {
        if ($this->singleton) {
            return isset($this->definations[$className]);
        }
        return isset($this->tmp[$className]);
    }
}
