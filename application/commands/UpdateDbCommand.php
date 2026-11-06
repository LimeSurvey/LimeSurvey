<?php

    /*
 * @author Denis Chenu <denis@sondages.pro>
 * @license GPL v3
 * @version 0.1
 *
 * Usage: application/commands/console.php updatedb [pathToConfig.php]
 *
 * If a path to a custom config.php is given as first argument, its 'components.db'
 * connection is used instead of the default application/config/config.php one,
 * similar to InstallFromConfigCommand.
 *
 * Copyright (C) 2017 LimeSurvey Team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */
class UpdateDBCommand extends LSConsoleCommand
{
    /**
     * Performs a database schema upgrade when the configured target version is greater than the current installed version.
     *
     * @param array|null $args Optional command-line arguments. $args[0], if set, is the path to a custom
     * config.php whose database connection should be used instead of the default one.
     * @throws CException If the current database version is not found (application appears uninstalled) or if the upgrade process fails and requires manual intervention.
     */
    #[\Override]
    public function run($args = null)
    {
        $usingCustomConfig = isset($args) && isset($args[0]) && $args[0];
        if ($usingCustomConfig) {
            $this->useCustomConfig($args[0]);
        }

        $newDbVersion = (int) Yii::app()->getConfig('dbversionnumber');
        $currentDbVersion = $usingCustomConfig
            ? $this->getCurrentDbVersion()
            : intval(Yii::app()->getConfig('DBVersion'));

        if (!$currentDbVersion) {
            throw new CException("Database version was not found, LimeSurvey is not correctly installed.");
        }

        if ($newDbVersion > $currentDbVersion) {
            echo "Update " . Yii::app()->db->connectionString . " with prefix :";
            echo Yii::app()->db->tablePrefix . " from {$currentDbVersion} to {$newDbVersion}\n";
            Yii::import('application.helpers.common_helper', true);
            Yii::import('application.helpers.update.update_helper', true);
            Yii::import('application.helpers.update.updatedb_helper', true);

            $incompleteUpdate = getIncompleteDatabaseUpdateMarker();
            if ($incompleteUpdate !== null) {
                throw new CException("A previous database update (starting from database version {$incompleteUpdate}) failed or was interrupted, so the database is probably in an inconsistent state. Please restore the database backup you made before the update, then run the update again.");
            }
            $result = db_upgrade_all($currentDbVersion, false, $abortReason);
            if ($result) {
                echo "Database has been successfully upgraded to version $newDbVersion \n";
                return 0;
            } elseif ($abortReason !== null) {
                // Refused before any update step ran, the database was not changed
                throw new CException($abortReason);
            } else {
                throw new CException("The database update failed, so the database is probably in an inconsistent state. Please restore the database backup you made before the update, then run the update again.");
            }
        } else {
            echo "no need update : DB is up-to-date\n";
            return 0;
        }
    }

    /**
     * Reads the current database version directly from settings_global rather than the application
     * config cached at bootstrap, which was read from the default database and would be stale once
     * a custom config.php has switched the connection to a different database.
     *
     * @return int
     */
    protected function getCurrentDbVersion()
    {
        $row = Yii::app()->db->createCommand()
            ->select('stg_value')
            ->from('{{settings_global}}')
            ->where('stg_name=:stg_name', array(':stg_name' => 'DBVersion'))
            ->queryRow();
        return $row ? intval($row['stg_value']) : 0;
    }
}
