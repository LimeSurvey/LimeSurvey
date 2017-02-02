<?php
 /*
 * @author Denis Chenu <denis@sondages.pro>
 * @license GPL v3
 * @version 0.1
 *
 * Copyright (C) 2017 Denis Chenu
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
    public function run(){
        $newDbVersion = (float)Yii::app()->getConfig('dbversionnumber');
        $currentDbVersion = (float)Yii::app()->getConfig('DBVersion');
        if(!$currentDbVersion){
            throw new CException("DB error : LimeSurvey database seems invalid.");
        }
        if($newDbVersion > $currentDbVersion){
            echo "Update ".Yii::app()->db->connectionString.", prefix :".Yii::app()->db->tablePrefix." from {$currentDbVersion} to {$newDbVersion}\n";
            Yii::import('application.helpers.common_helper', true);
            Yii::import('application.helpers.update.updatedb_helper', true);
            $result=db_upgrade_all($currentDbVersion);/* @todo : fix bad echoing here */
            if ($result) {
                //printf(gT("Database has been successfully upgraded to version %s"),$dbversionnumber)."\n";
                echo "Database has been successfully upgraded to version $newDbVersion \n";
            } else {
                //echo gT("Please fix this error in your database and try again")."\n";
                throw new CException("Please fix this error in your database and try again");
            }
        } else {
            echo "no need update ".$newDbVersion ." ". $currentDbVersion ."\n";
        }
    }
}
?>
