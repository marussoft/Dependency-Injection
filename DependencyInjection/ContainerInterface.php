<?php

namespace Marussia\Components\DependencyInjection;


interface ContainerInterface
{

    // Возвращает объект из контейнера по $class_name
    public function get(string $class_name);

    // Проверяет есть ли в контейнере объект $class_name
    public function has(string $class_name) : bool;
    
    // Возвращает новый объекьт
    public function instance(string $class_name, array $params, bool $singletone);

} 
