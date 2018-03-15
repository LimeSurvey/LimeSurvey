<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
    $currentDBVersion = GetGlobalSetting('DBVersion');
    if (intval($dbversionnumber) > intval($currentDBVersion)) {
        Yii::app()->loadHelper('update/updatedb');
        if (isset($subaction) && $subaction == "yes") {
            $header = Yii::app()->getController()->_getAdminHeader(false, true);
            $header = preg_replace('/<###begin###>/', '', $header);
            echo $header;
            $result = db_upgrade_all(intval($currentDBVersion));
            if ($result) {
                $data =
                '<div class="jumbotron message-box">'.
                    '<h2 class="">'.gT('Success').'</h2>'.
                    '<p class="lead">'.
                        sprintf(gT("Database has been successfully upgraded to version %s"), $dbversionnumber).
                    '</p>'.
                    '<p>'.
                        '<a href="'.Yii::app()->getController()->createUrl("/admin").'">'.gT("Back to main menu").'</a>'.
                    '</p>'.
                    '</div>';
            } else {
                $msg = '';
                foreach (yii::app()->user->getflashes() as $key => $message) {
                    $msg .= '<div class="alert alert-danger flash-'.$key.'">'.$message."</div>\n";
                }
                $data = $msg."<p><a href='".Yii::app()->getController()->createUrl("/admin/databaseupdate/sa/db")."'>".gT("Please fix this error in your database and try again")."</a></p></div> ";
            }
            return $data;
        } else {
            return ShowDBUpgradeNotice();
        }
    } else {
        Yii::app()->user->setFlash(
            'error',
            sprintf(
                gT('An internal error occured during the database upgrade. Please go back to the %smain menu%s. Contact support if the error persists.'),
                '<a href="' . Yii::app()->getController()->createUrl("/admin") . '">',
                '</a>'
            )
        );
    }
}

/**
 * @return string html
 */
function ShowDBUpgradeNotice()
{
    $message = Yii::app()->getController()->renderPartial('/admin/databaseupdate/verify', null, true);
    return $message;
}

/**
 * @param string $sProperty
 */
function getDBConnectionStringProperty($sProperty)
{
    // Yii doesn't give us a good way to get the database name
    preg_match('/'.$sProperty.'=([^;]*)/', Yii::app()->db->getSchema()->getDbConnection()->connectionString, $aMatches);
    if (count($aMatches) === 0) {
        return null;
    }
    return $aMatches[1];
}
