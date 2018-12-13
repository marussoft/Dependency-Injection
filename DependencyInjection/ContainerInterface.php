<?php

namespace Marussia\Components\DependencyInjection;


interface ContainerInterface
{

    // Возвращает объект из контейнера по $class_name
    public function get($class_name);

    // Проверяет есть ли в контейнере объект $class_name
    public function has(string $class_name);
    
    // Возвращает новый объекьт
    public function instance(string $class_name, array $params, bool $singletone);

} 
