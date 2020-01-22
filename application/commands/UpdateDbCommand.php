<?php

    /*
 * @author Denis Chenu <denis@sondages.pro>
 * @license GPL v3
 * @version 0.1
 *
 * Usage: application/commands/console.php updatedb
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
class UpdateDBCommand extends CConsoleCommand
{
    /**
     * Update database
     * @param array $args
     * @return void
     * @throws CException
     */
    public function run($args = null)
    {
        $newDbVersion = (int) Yii::app()->getConfig('dbversionnumber');
        $currentDbVersion = intval(Yii::app()->getConfig('DBVersion'));

        if (!$currentDbVersion) {
            throw new CException("DataBase version are not found, seems LimeSurvey are not installed.");
        }

        if ($newDbVersion > $currentDbVersion) {
            echo "Update ".Yii::app()->db->connectionString." with prefix :";
            echo Yii::app()->db->tablePrefix." from {$currentDbVersion} to {$newDbVersion}\n";
            Yii::import('application.helpers.common_helper', true);
            Yii::import('application.helpers.update.updatedb_helper', true);
            $result = db_upgrade_all($currentDbVersion);
            if ($result) {
                echo "Database has been successfully upgraded to version $newDbVersion \n";
            } else {
                throw new CException("Please fix this error in your database and try again");
            }
        } else {
            echo "no need update : DB is uptodate\n";
        }
    }
}
