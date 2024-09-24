<?php

class DummyMailer extends PluginBase
{
    protected static $description = 'Marks all emails as sent';
    protected static $name = 'DummyMailer';

    private $errorToReturn = null;

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    public function init()
    {
        $this->subscribe('beforeEmail', 'beforeEmail');
        $this->subscribe('beforeSurveyEmail', 'beforeEmail');
        $this->subscribe('beforeTokenEmail', 'beforeEmail');
    }

    public function beforeEmail()
    {
        $event = $this->getEvent();
        $event->set('send', false);
        $event->set('error', $this->errorToReturn);
    }

    public function setError($error)
    {
        $this->errorToReturn = $error;
    }

    public function reset()
    {
        $this->errorToReturn = null;
    }
}
