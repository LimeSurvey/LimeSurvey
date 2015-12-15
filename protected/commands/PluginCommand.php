<?php
namespace ls\cli;
use CConsoleCommand;

use ls\models\SettingGlobal;
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


    public function actionAuthenticate()
    {
        $enabled = \ls\models\SettingGlobal::get('authenticationPlugins');
        foreach($this->pluginManager->getAuthenticators() as $id => $plugin) {
            echo "{$plugin->getName()} ({$id}) ";
            if (in_array($id, $enabled)) {
                echo "ENABLED\n";
            } else {
                echo "DISABLED\n";
            }
        }
    }

    public function actionEnableAuthenticator($id)
    {
        if (array_key_exists($id, $this->pluginManager->getAuthenticators())) {
            $enabled = SettingGlobal::get('authenticationPlugins');
            $enabled[] = $id;
            echo SettingGlobal::set('authenticationPlugins', array_unique($enabled)) ? "SUCCESS\n" : "FAIL\n";
        }
    }

    public function actionDisableAuthenticator($id)
    {
        $enabled = array_flip(SettingGlobal::get('authenticationPlugins'));
        if (isset($id, $enabled)) {
            array_flip($enabled);
            unset($enabled[$id]);
            echo SettingGlobal::set('authenticationPlugins', array_unique(array_keys($enabled))) ? "SUCCESS\n" : "FAIL\n";
        }
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

