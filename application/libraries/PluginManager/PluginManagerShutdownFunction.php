<?php

namespace LimeSurvey\PluginManager;

/**
 * Used as shutdown function during plugin loading, to turn off 
 * plugins that causes troubles.
 *
 * @see https://stackoverflow.com/questions/2726524/can-you-unregister-a-shutdown-function
 * @see http://de2.php.net/manual/en/class.error.php
 * @see http://de2.php.net/manual/en/function.register-shutdown-function.php
 */
class PluginManagerShutdownFunction
{

    /**
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @var string
     */
    protected $currentPluginName;

    /**
     * Enable object.
     * The object should ONLY be enabled during the plugin load phase.
     * @return void
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable object.
     * @return void
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * 
     */
    public function setPluginName($name)
    {
        $this->currentPluginName = $name;
    }

    /**
     * Magic method to let object be called as function.
     * @return void
     */
    public function __invoke()
    {
        if (!$this->enabled) {
            return;
        }

        $error = error_get_last();

        if (empty($error)) {
            $error['message'] = 'Unknown error - error_get_last() returned null';
            $error['file'] = '';
        }

        if (empty($this->currentPluginName)) {
            // Internal error - adjust the logic in PluginManager.
            echo 'ERROR: No currentPluginName';
            return;
        }

        $plugin = \Plugin::model()->find('name = :name', [':name' => $this->currentPluginName]);

        if ($plugin) {
            $result = $plugin->setLoadError($error);
        } else {
            $plugin = new \Plugin();
            $plugin->name = $this->currentPluginName;
            $plugin->active = 0;
            $result1 = $plugin->save();
            $result2 = $plugin->setLoadError($error);
            $result = $result1 && $result2;
        }

        $this->showError(
            [
                'result' => $result,
                'error'  => $error
            ]
        );

    }

    /**
     * Echo error message.
     * @param array $data
     * @return void
     */
    protected function showError(array $data)
    {
        echo '<h1>';
        printf('Fatal plugin error: %s', $this->currentPluginName);
        echo '</h1>';

        echo '<h2>';
        printf($data['error']['message']);
        echo '</h2>';

        echo '<p>';
        if ($data['result']) {
            printf('This plugin has been marked as faulty and will not be loaded again. See the plugin manager for more details, or contact the plugin author.');
        } else {
            printf('This plugin could not be updated. Please contact support.');
        }
        echo '</p>';
    }
}
