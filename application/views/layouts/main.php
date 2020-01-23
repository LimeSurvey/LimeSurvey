<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);


$aData = Yii::app()->getController()->aData;

// ###################################################  HEADER #####################################################

LayoutHelper::showHeaders($aData);
//################################################# END HEADER #######################################################


// --> source code from Survey_Common_Action ...
//The adminmenu bar will be generated from /admin/super/adminmenu.php
//$this->_showadminmenu($aData);
//################################################## ADMIN MENU #####################################################
LayoutHelper::showadminmenu($aData);

// Generated through /admin/usergroup/usergroupbar_view
LayoutHelper::userGroupBar($aData);  //todo it should be moved to new UserGroupController and it's actions

echo "<!-- BEGIN LAYOUT_MAIN -->";

LayoutHelper::fullpagebar($aData);

LayoutHelper::updatenotification();

LayoutHelper::notifications();

    
    //The load indicator for pjax
    echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

    echo '<!-- Full page, started in Survey_Common_Action::render_wrapped_template() -->
                <div class="container-fluid full-page-wrapper" id="in_survey_common_action">
                    ';

    echo $content;

    echo '</div>';


// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    LayoutHelper::loadEndScripts();
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        LayoutHelper::getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
    }
} else {
    echo '</body>
    </html>';
}

/**
    
    // Footer
    if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
       // Yii::app()->getController()->_loadEndScripts();
        //echo 'this is the footer ...';
    }

    if (!Yii::app()->user->isGuest) {
        if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
           // Yii::app()->getController()->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));

            $aData['versionnumber'] = Yii::app()->getConfig("versionnumber");

            $aData['buildtext'] = "";
            if (Yii::app()->getConfig("buildnumber") != "") {
                $aData['buildtext'] = "+".Yii::app()->getConfig("buildnumber");
            }

            //If user is not logged in, don't print the version number information in the footer.
            if (empty(Yii::app()->session['loginID'])) {
                $aData['versionnumber'] = "";
                $aData['versiontitle'] = "";
                $aData['buildtext'] = "";
            } else {
                $aData['versiontitle'] = gT('Version');
            }

            $aData['imageurl'] = Yii::app()->getConfig("imageurl");
            $aData['url'] = 'http://manual.limesurvey.org';
            $this->renderPartial("/admin/super/footer", $aData, false);
        }
    } else {
        echo '</body>
        </html>';
    }
 *
 */
