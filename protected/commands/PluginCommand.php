<?php
namespace ls\cli;
use CConsoleCommand;

use ls\pluginmanager\PluginConfig;
use ls\pluginmanager\PluginManager;
use SebastianBergmann\Environment\Console;

/**
 * Class PluginCommand
 * @package ls\cli
 *
 * @property PluginManager $pluginManager
 */
class PluginCommand extends CConsoleCommand
{
    public $connection;

    protected function getPluginManager()
    {
        return App()->pluginManager;
    }

    public function actionCron($interval)
    {

        $event = new PluginEvent('cron');
        $event->set('interval', $interval);
        $this->pluginManager->dispatchEvent($event);


    }

    public function actionIndex()
    {
        echo "Scanning plugins folders...";
        $result = $this->pluginManager->scanPlugins();
        var_dump(array_keys($result));
        echo "OK\n";
        $plugins = $this->pluginManager->getPlugins();
        foreach($result as $pluginConfig) {
            echo "Found {$pluginConfig->name} ({$pluginConfig->getId()})";
            if (array_key_exists($pluginConfig->getId(), $plugins)) {
                echo " ENABLED\n";
            } else {
                echo " DISABLED\n";
            }
        }
    }

    public function actionEnable($id)
    {
        $result = $this->pluginManager->scanPlugins();
//        if ()
        $this->pluginManager->enablePlugin($id);
    }

    public function actionDisable($id)
    {
        $this->pluginManager->disablePlugin($id);
    }
}

