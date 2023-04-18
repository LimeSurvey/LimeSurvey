<?php

class SettingsPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing SettingsPlugin event';
    protected static $name = 'SettingsPlugin';
    protected $storage = 'DbStorage';
    protected $encryptedSettings = [];
    /* @inheritdoc */
    protected $settings = [];

    /**
     * Set the settings, used to test some settings
     * @param array[]
     * @return void
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

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

    public function setSurveySetting($name, $value, $surveyId)
    {
        return $this->set($name, $value, 'Survey', $surveyId);
    }

    public function getSurveySetting($name, $surveyId)
    {
        return $this->get($name, 'Survey', $surveyId);
    }
}
