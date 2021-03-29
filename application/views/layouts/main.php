<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);


$aData = Yii::app()->getController()->aData;

$layoutHelper = new LayoutHelper();

// ###################################################  HEADER #####################################################
$layoutHelper->showHeaders($aData);
//################################################# END HEADER #######################################################


//################################################## ADMIN MENU #####################################################
$layoutHelper->showadminmenu($aData);

// Generated through /admin/usergroup/usergroupbar_view

$layoutHelper->renderMenuBar($aData);

echo "<!-- BEGIN LAYOUT_MAIN -->";

$layoutHelper->fullpagebar($aData);

$layoutHelper->updatenotification();

$layoutHelper->notifications();

    
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
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        $layoutHelper->getAdminFooter('http://manual.limesurvey.org');
    }
} else {
    echo '</body>
    </html>';
}
