<?php

namespace Marussia\DependencyInjection;

interface ContainerInterface
{

    // Возвращает объект из контейнера по $className
    public function get(string $className);

    // Проверяет есть ли в контейнере объект $className
    public function has(string $className) : bool;
    
    // Возвращает новый объекьт
    public function instance(string $className, array $params, bool $singletone);

} 
