<?php

namespace LimeSurvey;

use CActiveRecord;

class DI
{
    private static $container = null;

    public static function getContainer()
    {
        if (!static::$container) {
            static::$container = static::makeContainer();
        }
        return static::$container;
    }

    public static function makeContainer()
    {
        $container = new \DI\Container;

        // Type hinting on a Yii model / active-record class should return its
        // - static instance e.g Survey::model(). It is not possible to type hint
        // - on this, so instead we configure the container to return the correct
        // - object based on the call time class name whenever we type hint on
        // - CActiveRecord or anything that extends CActiveRecord
        $container->set('CActiveRecord', function (CActiveRecord $entry) {
            $class = $entry->getName();
            return $class::model();
        });

        $container->set('LSYii_Application', function () {
            return App();
        });

        $container->set('PluginManager', function () {
            return App()->getPluginManager();
        });

        return $container;
    }
}
