<?php

use LimeSurvey\PluginManager\SmtpOAuthPluginBase;

class EmailPlugin extends SmtpOAuthPluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Dummy plugin for testing the EmailPluginBase class.';
    protected static $name = 'EmailPlugin';

    protected $credentialAttributes = ['clientId', 'clientSecret'];

    protected function getDisplayName()
    {
        return 'Test';
    }

    protected function getProvider($credentials)
    {
        return true;
    }

    protected function getAuthorizationOptions()
    {
        return true;
    }

    protected function getOAuthConfigForMailer()
    {
        return true;
    }

    public function isTestCurrentEmailPlugin()
    {
        return $this->isCurrentEmailPlugin();
    }

    public function saveTestPluginSettings($settings)
    {
        $this->saveSettings($settings);
    }

    public function getTestPluginCredentials()
    {
        return $this->getCredentials();
    }
}
