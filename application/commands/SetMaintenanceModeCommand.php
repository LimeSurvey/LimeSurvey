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
 * Sets the maintenance mode of an installation from the command line, see the setting
 * "maintenancemode" in config-defaults.php. The mode "hard-cli" locks the administration
 * (web interface, RemoteControl and REST API) as well as survey taking, so that nothing
 * can be changed while a hosting system makes a backup or runs the database update
 * (console.php updatedb). It can only be set and unset with this command, not in the
 * global settings.
 *
 * The value is stored in the settings_global table, so it takes precedence over config.php
 * and survives file updates of the installation.
 */
class SetMaintenanceModeCommand extends LSConsoleCommand
{
    /**
     * @return string
     */
    #[\Override]
    public function getHelp()
    {
        return "Usage: console.php setMaintenanceMode off|soft|hard|hard-cli [path/to/config.php]\n"
            . "  off       no maintenance\n"
            . "  soft      participants can finish surveys they already started, no new participants are allowed\n"
            . "  hard      no participant can take a survey\n"
            . "  hard-cli  like hard, and the administration (web interface, RemoteControl, REST API) is locked too;\n"
            . "            can only be set and unset with this command, e.g. while a backup or console.php updatedb is run\n"
            . "The config.php of another installation can be given to work on its database.\n";
    }

    /**
     * @param array $args The mode, optionally followed by the path of a config.php
     * @return int 0 on success, 1 on wrong usage, an unusable config.php or a mode that could not be saved
     */
    #[\Override]
    public function run($args)
    {
        $mode = strtolower((string) ($args[0] ?? ''));
        $modes = [
            SettingGlobal::MAINTENANCE_MODE_OFF,
            SettingGlobal::MAINTENANCE_MODE_SOFT,
            SettingGlobal::MAINTENANCE_MODE_HARD,
            SettingGlobal::MAINTENANCE_MODE_HARD_CLI,
        ];
        if (!in_array($mode, $modes, true)) {
            echo $this->getHelp();
            return 1;
        }
        if (!empty($args[1])) {
            try {
                $this->useCustomConfig($args[1]);
            } catch (CException $e) {
                echo $e->getMessage() . "\n" . $this->getHelp();
                return 1;
            }
        }
        SettingGlobal::setSetting('maintenancemode', $mode);
        // Read back from the database: only report success when the mode is really stored
        $storedMode = Yii::app()->db->createCommand()
            ->select('stg_value')
            ->from('{{settings_global}}')
            ->where('stg_name = :name', [':name' => 'maintenancemode'])
            ->queryScalar();
        if ($storedMode !== $mode) {
            echo "The maintenance mode could not be saved, it is still '" . ($storedMode === false ? SettingGlobal::MAINTENANCE_MODE_OFF : $storedMode) . "'.\n";
            return 1;
        }
        echo "Maintenance mode set to '$mode'.\n";
        return 0;
    }
}
