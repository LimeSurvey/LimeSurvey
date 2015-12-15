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
        foreach($result as $pluginConfig) {
            if ($pluginConfig->getId() === $id) {
                echo "Plugin found. ";
                echo $this->pluginManager->enablePlugin($id) ? "OK" : "FAILED";
                echo "\n";
                return;
            }
        }
        echo "Plugin not found.\n";

    }

    public function actionDisable($id)
    {
        if ($this->pluginManager->getPlugin($id) !== null) {
            echo "Plugin found. ";

            echo $this->pluginManager->disablePlugin($id) ? "OK" : "FAILED";
            echo "\n";

        } else {
            echo "Plugin not found.\n";
        }

    }
}

