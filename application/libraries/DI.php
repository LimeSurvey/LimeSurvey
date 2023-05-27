<?php

namespace LimeSurvey;

class DI
{
    private static $container = null;

    public static function getContainer()
    {
        if (!static::$container) {
            static::$container = static::newContainer();
        }
        return static::$container;
    }

    public static function newContainer()
    {
        return new \DI\Container;
    }
}
