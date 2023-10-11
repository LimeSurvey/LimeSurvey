<?php

namespace LimeSurvey;

use CActiveRecord;
use LSYii_Application;
use LimeSurvey\PluginManager\PluginManager;
use CHttpSession;


/**
 * Dependency Injection
 *
 * Provides a access to configured DI container instance.
 * Addition makeContainer() method allows for container
 * customisation when unit testing.
 */
class DI
{
    private static $container = null;

    /**
     * Get DI container instance
     *
     * Singleton pattern.
     *
     * @return \DI\Container
     */
    public static function getContainer()
    {
        if (!static::$container) {
            static::$container = static::makeContainer();
        }
        return static::$container;
    }

    /**
     * Make new DI container instance
     *
     * @return \DI\Container
     */
    public static function makeContainer()
    {
        $container = new \DI\Container;

        // Type hinting on a Yii model / active-record class should return its
        // - static instance e.g Survey::model(). It is not possible to type hint
        // - on this, so instead we configure the container to return the correct
        // - object based on the call time class name, whenever we type hint on
        // - CActiveRecord or anything that extends CActiveRecord
        $container->set(CActiveRecord::class, function (CActiveRecord $entry) {
            $class = $entry->getName();
            return $class::model();
        });

        $container->set(LSYii_Application::class, function () {
            return App();
        });

        $container->set(PluginManager::class, function () {
            return App()->getPluginManager();
        });

        $container->set(CHttpSession::class, function () {
            return App()->session;
        });

        return $container;
    }
}
