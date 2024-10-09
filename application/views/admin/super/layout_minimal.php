<?php

//All paths relative from /application/views

//headers will be generated with the template file /admin/super/header.php
$this->showHeaders($aData);

//The adminmenu bar will be generated from /admin/super/adminmenu.php

echo "<!-- BEGIN LAYOUT_MAIN -->";
// Generated through /admin/super/fullpagebar_view
$this->updatenotification();
$this->notifications();

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

echo '<!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
    <div class="container-fluid" id="in_survey_common_action">
';

echo $content;

echo '</div>';
echo "<!-- END LAYOUT_MAIN -->";

// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    Yii::app()->getController()->loadEndScripts();
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        Yii::app()->getController()->getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
    }
} else {
    echo '</body>
        </html>';
}
