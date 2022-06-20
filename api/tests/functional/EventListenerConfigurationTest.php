<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Yii\Event\ListenerConfigurationChecker;
use Yiisoft\Yii\Runner\ConfigFactory;

use function dirname;

class EventListenerConfigurationTest extends TestCase
{
    public function testConsoleListenerConfiguration(): void
    {
        // TODO: Error related to YII_ENV
        $this->markTestSkipped();

        $config = ConfigFactory::create(new ConfigPaths(dirname(__DIR__, 2), 'config'), null);

        $containerConfig = ContainerConfig::create()
            ->withDefinitions($config->get('console'));
        $container = (new Container($containerConfig))->get(ContainerInterface::class);
        $checker = $container->get(ListenerConfigurationChecker::class);
        $checker->check($config->get('events-console'));

        self::assertInstanceOf(ListenerConfigurationChecker::class, $checker);
    }
}
