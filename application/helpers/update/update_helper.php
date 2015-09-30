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
        if(isset($subaction) && $subaction=="yes")
        {
            echo Yii::app()->getController()->_getAdminHeader();
        	//echo "<div style='width:90%; padding:1% 5%;background-color:#eee;'>";
            Yii::app()->loadHelper('update/updatedb');
            $result=db_upgrade_all(intval($currentDBVersion));
            if ($result)
            {
                $data = '<div class="message-box jumbotron">';
                $data .= '<h2 class="text-success">'.gT("Success").'</h2>';
                $data .= "<p class='lead text-success'>".sprintf(gT("Database has been successfully upgraded to version %s"),$dbversionnumber).'</p>';
                $data .= "<p><a class='btn btn-default btn-lg' href='".Yii::app()->getController()->createUrl("/admin")."'>".gT("Back to main menu")."</a></p></div>";
            }
            else
            {
                $data = '<div class="message-box jumbotron message-box-error">';
                $data .= '<h2 class="text-warning">'.gT("Error").'</h2>';
                $data .= '<p class="lead text-warning">';
                $data .=     gT('An non-recoverable error happened during the update. Error details:');
                $data .= '</p>';
                $data .= '<p>'.Yii::app()->session['dbError'].'</p>';
                $data .= "<p>".gT("Please fix this error in your database and try again")."</p>";
                $data .= "<a class='btn btn-default btn-lg' href='".Yii::app()->getController()->createUrl("/admin/update/sa/db")."'>".gT("Continue")."</a>";
                $data .= "</div>";
            }
            return $data;
        }
        else {
            return ShowDBUpgradeNotice();
        }
    }
}

function ShowDBUpgradeNotice() {
    //$error=false;

	//$sitename = Yii::app()->getConfig('sitename');
	return '<div class="message-box jumbotron message-box-warning">'
    ."<h2 class='text-warning'>".gT('Database upgrade').'</h2>'
    .'<p class="lead text-warning">'.gT('Please verify the following information before continuing with the database upgrade:').'</p><ul class="list-unstyled">'
    ."<li><b>" .gT('Database type') . ":</b> " . Yii::app()->db->getDriverName() . "</li>"
    ."<li><b>" .gT('Database name') . ":</b> " . getDBConnectionStringProperty('dbname') . "</li>"
    ."<li><b>" .gT('Table prefix') . ":</b> " . Yii::app()->db->tablePrefix . "</li>"
    ."<li><b>" .gT('Site name') . ":</b> " . Yii::app()->getConfig("sitename") . "</li>"
    ."<li><b>" .gT('Root URL') . ":</b> " . Yii::app()->getController()->createUrl('') . "</li>"
    .'</ul><br/>'
    ."<p>"
    ."<a class='btn btn-default btn-lg' href='".Yii::app()->getController()->createUrl("admin/update/sa/db/continue/yes")."'>" . gT('Continue') . "</a>"
    ."</p>"
	.'</div>';
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
