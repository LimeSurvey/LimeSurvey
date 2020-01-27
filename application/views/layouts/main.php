<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);


$aData = Yii::app()->getController()->aData;

$layoutHelper = new LayoutHelper();

// ###################################################  HEADER #####################################################
$layoutHelper->showHeaders($aData);
//LayoutHelper::showHeaders($aData);
//################################################# END HEADER #######################################################


// --> source code from Survey_Common_Action ...
//The adminmenu bar will be generated from /admin/super/adminmenu.php
//$this->_showadminmenu($aData);
//################################################## ADMIN MENU #####################################################
$layoutHelper->showadminmenu($aData);
//LayoutHelper::showadminmenu($aData);

// Generated through /admin/usergroup/usergroupbar_view

//todo it should be moved to new UserGroupController and it's actions
//$layoutHelper->userGroupBar($aData);

echo "<!-- BEGIN LAYOUT_MAIN -->";

$layoutHelper->fullpagebar($aData);
//LayoutHelper::fullpagebar($aData);

$layoutHelper->updatenotification();
//LayoutHelper::updatenotification();

$layoutHelper->notifications();
//LayoutHelper::notifications();

    
    //The load indicator for pjax
    echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

    echo '<!-- Full page, started in Survey_Common_Action::render_wrapped_template() -->
                <div class="container-fluid full-page-wrapper" id="in_survey_common_action">
                    ';

    echo $content;

    echo '</div>';


// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    $layoutHelper->loadEndScripts();
    //LayoutHelper::loadEndScripts();
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        //LayoutHelper::getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
        $layoutHelper->getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
    }
} else {
    echo '</body>
    </html>';
}


