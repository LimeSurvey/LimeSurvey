<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * This functions checks if the databaseversion in the settings table is the same one as required
 * If not then the necessary upgrade procedures are run
 */
function CheckForDBUpgrades($subaction = null)
{
    $dbversionnumber = Yii::app()->getConfig('dbversionnumber');
    $currentDBVersion=GetGlobalSetting('DBVersion');
    $usertemplaterootdir = Yii::app()->getConfig('usertemplaterootdir');
    $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
    if (intval($dbversionnumber)>intval($currentDBVersion))
    {
        Yii::app()->loadHelper('update/updatedb');
         if(isset($subaction) && $subaction=="yes")
        {
            echo Yii::app()->getController()->_getAdminHeader();
            $result=db_upgrade_all(intval($currentDBVersion));
            if ($result)
            {
                $data =
                '<div class="jumbotron message-box">'.
                    '<h2 class="">'.gT('Success').'</h2>'.
                    '<p class="lead">'.
                        sprintf(gT("Database has been successfully upgraded to version %s"),$dbversionnumber).
                    '</p>'.
                    '<p>'.
                        '<a href="'.Yii::app()->getController()->createUrl("/admin").'">'.gT("Back to main menu").'</a>'.
                    '</p>'.
                 '</div>';
            }
            else
            {
                $data = "<p><a href='".Yii::app()->getController()->createUrl("/admin/databaseupdate/sa/db")."'>".gT("Please fix this error in your database and try again")."</a></p></div>";
            }
            return $data;
        }
        else {
            return ShowDBUpgradeNotice();
        }
    }
}

function ShowDBUpgradeNotice() {
    $message ='
        <div class="jumbotron message-box">
            <h2 class="">'.gT('Database upgrade').'</h2>
            <p class="lead">'.gT('Please verify the following information before continuing with the database upgrade:').'</p>
            <div class="row">
            <div class="col-md-offset-4 col-md-4">
                <table class="table table-striped">
                    <tr><th>'.gT('Database type:') . '</th><td>' . Yii::app()->db->getDriverName() . '</td></tr>
                    <tr><th>'.gT('Database name:') . '</th><td>' . getDBConnectionStringProperty('dbname') . '</td></tr>
                    <tr><th>'.gT('Table prefix:') . '</th><td>' . Yii::app()->db->tablePrefix . '</td></tr>
                    <tr><th>'.gT('Site name:') . '</th><td>' . Yii::app()->getConfig("sitename") . '</td></tr>
                    <tr><th>'.gT('Root URL:') . '</th><td>' . Yii::app()->getController()->createUrl('') . '</td></tr>
                    <tr><th>'.gT('Current database version:') . '</th><td>' . GetGlobalSetting('DBVersion') . '</td></tr>
                    <tr><th>'.gT('Target database version:') . '</th><td>' . Yii::app()->getConfig('dbversionnumber'). '</td></tr>
                </table>
            </div>
            </div>

            <p>
                <a class="btn btn-lg btn-success" href="'.Yii::app()->getController()->createUrl("admin/databaseupdate/sa/db/continue/yes").'" role="button">
                    '. gT('Click here to continue') .'
                </a>
            </p>

        </div>
    ';

    return $message;
}

function getDBConnectionStringProperty($sProperty)
{
    // Yii doesn't give us a good way to get the database name
    preg_match('/'.$sProperty.'=([^;]*)/', Yii::app()->db->getSchema()->getDbConnection()->connectionString, $aMatches);
    if ( count($aMatches) === 0 ) {
        return null;
    }
    return $aMatches[1];
}
