<?php

namespace LimeSurvey;

use CActiveRecord;
use LSYii_Application;
use LimeSurvey\PluginManager\PluginManager;
use CHttpSession;
use CDbConnection;
use DI\{
    ContainerBuilder,
    ContainerInterface
};

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
        $builder->addDefinitions([
            LSYii_Application::class => function () {
                //exit('C');
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
            }
        ]);

        return $builder->build();
    }

    private static function getActiveRecordDefinitions()
    {
        $modelClasses = [
            \Answer::class,
            \AnswerL10n::class,
            \Survey::class,
            \SurveyLanguageSetting::class,
            \Question::class,
            \QuestionAttribute::class,
            \QuestionCreate::class,
            \QuestionGroup::class,
            \QuestionL10n::class,
            \User::class,
            \UserGroup::class,
        ];
        $defintions = [];
        foreach ($modelClasses as $modelClass) {
            $defintions[$modelClass] = \DI\factory(
                function (ContainerInterface $c) use ($modelClass) {
                    return $modelClass::model();
                }
            );
        }
        return $defintions;
    }
}
