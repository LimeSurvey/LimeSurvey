<?php

namespace LimeSurvey;

use ArchivedTableSettings;
use DI\ContainerBuilder;
use CActiveRecord;
use LSYii_Application;
use LimeSurvey\PluginManager\PluginManager;
use CHttpSession;
use CDbConnection;

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
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            LSYii_Application::class => function () {
                return App();
            },
            PluginManager::class => function () {
                return App()->getPluginManager();
            },
            CHttpSession::class => function () {
                return App()->session;
            },
            CDbConnection::class => function () {
                return App()->db;
            },
            'archivedTokenSettings' => \DI\create(ArchivedTableSettings::class),
            'archivedTimingsSettings' => \DI\create(ArchivedTableSettings::class),
            'archivedResponseSettings' => \DI\create(ArchivedTableSettings::class),
        ]);

        return $builder->build();
    }
}
