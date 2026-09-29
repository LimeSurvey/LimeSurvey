<?php

namespace LimeSurvey\PluginManager;

use LimeMailer;
use LimeSurvey\Datavalueobjects\EmailPluginInfo;

abstract class EmailPluginBase extends PluginBase
{
    /**
     * Returns the plugin's display name
     * @return string
     */
    abstract protected function getDisplayName();

    /**
     * Returns true if the plugin is the currently selected email plugin
     * @return bool
     */
    protected function isCurrentEmailPlugin()
    {
        if (LimeMailer::MethodPlugin !== \Yii::app()->getConfig('emailmethod')) {
            return false;
        }
        return get_class($this) == \Yii::app()->getConfig('emailplugin');
    }

    /**
     * Returns the plugin's metadata
     * @return EmailPluginInfo
     */
    protected function getEmailPluginInfo()
    {
        return new EmailPluginInfo($this->getId(), $this->getDisplayName(), get_class($this));
    }
}
