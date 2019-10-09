<?php
/*
* LimeSurvey (tm)
* Copyright (C) 2011-2017 The LimeSurvey Project Team / Carsten Schmitz
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
     * php application/commands/console.php plugin events
     */
    public function actionEvents()
    {
        $basePath = Yii::getPathOfAlias('webroot');

        $i = new RecursiveDirectoryIterator($basePath);
        $i2 = new RecursiveIteratorIterator($i);

        echo '...scanning' . PHP_EOL;

        $events = array();
        foreach ($i2 as $file) {
            /* @var $file SplFileInfo */
            if (substr($file->getFileName(), -3, 3) == 'php') {
                echo '.';
                $this->scanFile($file->getPathname(), $events);
            }
        }

        echo PHP_EOL;

        $events = array_unique($events);
        sort($events);

        echo implode(PHP_EOL, $events);
        exit(0);
    }

    private function scanFile($fileName, &$events)
    {
        $contents = file_get_contents($fileName);

        $regex = '/(.*)new[[:space:]]+PluginEvent[[:space:]]*\([[:space:]]*[\'"]+(.*)[\'"]+/';

        $count = preg_match_all($regex, $contents, $matches);
        if ($count > 0) {
            $events = array_merge($events, $matches[2]);
        }
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
