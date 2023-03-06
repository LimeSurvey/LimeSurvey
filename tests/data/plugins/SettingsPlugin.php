<?php

class SettingsPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing SettingsPlugin event';
    protected static $name = 'SettingsPlugin';
    protected $storage = 'DbStorage';
    protected $encryptedSettings = [];
    /* @inheritdoc */
    protected $settings = [];

    public function init()
    {
    }

    public function setSetting($name, $value)
    {
        return $this->set($name, $value);
    }

    public function getSetting($name)
    {
        return $this->get($name);
    }

    public function setEncryptedSettings($encryptedSettings)
    {
        $this->encryptedSettings = $encryptedSettings;
    }

    /**
     * Set the settings, used to test some settings
     * @param array[]
     * @return void
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
