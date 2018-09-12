<?php
/*
* LimeSurvey
* Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
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
 * Render the admin footer including version and build info when logged in
 */
class AdminFooter extends CWidget
{
    public function run()
        {
            //If user is not logged in, don't print the version number information in the footer.
            if (empty(Yii::app()->session['loginID']))
            {
                $versionnumber="";
                $versiontitle="";
                $buildtext="";
            } else {
                $versionnumber = Yii::app()->getConfig("versionnumber");
                $versiontitle = gT('Version');
                $buildtext = "";
                if(Yii::app()->getConfig("buildnumber")!="") {
                   $buildtext = "+".Yii::app()->getConfig("buildnumber");
                }
            }

            $aData = array(
                'versionnumber' => $versionnumber,
                'versiontitle'  => $versiontitle,
                'buildtext'     => $buildtext
            );

            $this->render('footer', $aData);
        }
}