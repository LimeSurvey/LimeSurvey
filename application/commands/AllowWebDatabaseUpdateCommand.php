<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Switches whether a database update may be started from the web interface
 * (automatically after login or on the database update page), see the setting
 * "allowwebdatabaseupdate" in config-defaults.php. Meant for systems that run the
 * database update from the command line (console.php updatedb) and want to make
 * sure nobody starts it from a web request in the meantime.
 *
 * The value is stored in the settings_global table, so it takes precedence over
 * config.php and survives file updates of the installation.
 */
class AllowWebDatabaseUpdateCommand extends LSConsoleCommand
{
    /** @var string Name of the setting in settings_global and config-defaults.php */
    public const SETTING_NAME = 'allowwebdatabaseupdate';

    /**
     * @return string
     */
    #[\Override]
    public function getHelp()
    {
        return "Usage: console.php allowwebdatabaseupdate on|off [path/to/config.php] [FQDN]\n"
            . "  on   allow starting the database update from the web interface (default)\n"
            . "  off  refuse it, e.g. while the update is run with console.php updatedb\n"
            . "The config.php of another installation can be given to work on its database.\n";
    }

    /**
     * @param array $args "on" or "off", optionally followed by the path of a config.php
     * @return int 0 on success, 1 on wrong usage
     */
    #[\Override]
    public function run($args)
    {
        $mode = strtolower((string) ($args[0] ?? ''));
        if (!in_array($mode, ['on', 'off'], true)) {
            echo $this->getHelp();
            return 1;
        }
        if (!empty($args[1])) {
            $this->useCustomConfig($args[1]);
        }
        $value = ($mode === 'on') ? '1' : '0';
        $db = Yii::app()->db;
        // Not "update, insert when 0 rows were affected": MySQL reports 0 affected rows for an unchanged value
        $exists = $db->createCommand()
            ->select('stg_name')
            ->from('{{settings_global}}')
            ->where('stg_name = :name', [':name' => self::SETTING_NAME])
            ->queryScalar();
        if ($exists === false) {
            $db->createCommand()->insert('{{settings_global}}', ['stg_name' => self::SETTING_NAME, 'stg_value' => $value]);
        } else {
            $db->createCommand()->update('{{settings_global}}', ['stg_value' => $value], 'stg_name = :name', [':name' => self::SETTING_NAME]);
        }
        echo ($mode === 'on')
            ? "Database updates from the web interface are allowed again.\n"
            : "Database updates from the web interface are now refused until they are allowed again with 'allowwebdatabaseupdate on'.\n";
        return 0;
    }
}
