<?php

    /*
    * LimeSurvey (tm)
    * Copyright (C) 2011-2026 The LimeSurvey Project Team
    * All rights reserved.
    * License: GNU/GPL License v3 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
    */
class PluginCommand extends CConsoleCommand
{
    public $connection;

    /**
    * register some needed or a lot used part
    */
    public function init()
    {
        parent::init();
        Yii::import('application.helpers.common_helper', true);
    }

    /**
     * Call for cron action
     * @param int $interval Minutes for interval
     * @return void
     */
    public function actionCron($interval = null)
    {
        $pm = \Yii::app()->pluginManager;
        $event = new PluginEvent('cron');
        $event->set('interval', $interval);
        $pm->dispatchEvent($event);
    }

    /**
     * Call directly an event by command (it's default)
     * @param string $target Target of action, plugin name for example
     * @param mixed $function Extra parameters for plugin
     * @param mixed $option Extra parameters for plugin
     * @return void
     */
    public function actionIndex($target, $function = null, $option = null)
    {
        $pm = \Yii::app()->pluginManager;
        $event = new PluginEvent('direct');
        $event->set('target', $target);
        $event->set('function', $function);
        $event->set('option', $option);
        $pm->dispatchEvent($event);
    }
}
