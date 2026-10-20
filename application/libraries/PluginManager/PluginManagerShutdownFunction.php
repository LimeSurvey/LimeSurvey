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
        $this->currentPluginName = null;
        $this->enabled = false;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
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
     * Marks the plugin that caused the fatal error as faulty and shows the error.
     * A plugin that is not installed is not saved, so it is loaded again on next
     * scan of the plugin folders (e.g. after the plugin files were fixed).
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
        if (empty($plugin)) {
            $this->showError(
                [
                    'installed' => false,
                    'error'     => $error
                ]
            );
            return;
        }

        $result = \Plugin::handlePluginLoadError(
            $plugin,
            $this->currentPluginName,
            $error
        );

        $this->showError(
            [
                'installed' => true,
                'result'    => $result,
                'error'     => $error
            ]
        );
    }

    /**
     * Echo error message.
     * @param array $data Array with 'installed' (boolean), 'error' (array from error_get_last)
     *                    and, for installed plugins, 'result' (rows affected by saving the load error)
     * @return void
     */
    protected function showError(array $data)
    {
        echo '<h1>';
        printf('Fatal plugin error: %s', $this->currentPluginName);
        echo '</h1>';

        echo '<h2>';
        echo $data['error']['message'];
        echo '</h2>';

        echo '<p>';
        if (!$data['installed']) {
            echo 'This plugin is not installed. Fix or remove the plugin files, then scan the plugin files again.';
        } elseif ($data['result']) {
            echo 'This plugin has been marked as faulty and will not be loaded again. '
                . 'See the plugin manager for more details, or contact the plugin author.';
        } else {
            echo 'This plugin could not be updated. Please contact support.';
        }
        echo '</p>';
    }
}
