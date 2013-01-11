<?php
    /*
    * LimeSurvey (tm)
    * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
    */
    class UpdatedbCommand extends CConsoleCommand
    {
        public $connection;

        public function run($sArgument)
        {
            if (!isset($sArgument) || !isset($sArgument[0]) || $sArgument[0]!='yes') {
                die('This CLI command updates a LimeSurvey database. For security reasons this command can only started if you add the parameter \'yes\' to the command line.');   
            }
            Yii::import('application.helpers.common_helper', true);
            Yii::import('application.helpers.database_helper', true);
            $sVersionConfigPath=dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php';   
            $config=require ($sVersionConfigPath);         
            $iDBVersionNumber = $config['dbversionnumber'];
            $iCurrentDBVersion=Yii::app()->db->createCommand("select stg_value from {{settings_global}} where stg_name='DBVersion'")->queryScalar();;
            if (intval($iDBVersionNumber)>intval($iCurrentDBVersion))
            {
                Yii::import('application.helpers.update.updatedb_helper', true);
                db_upgrade_all((float)$iCurrentDBVersion);
                print 'Database updated to '.$iCurrentDBVersion;
            }
            else
            {
                print 'Database already at version '.$iCurrentDBVersion;
            }
        }
    }
?>