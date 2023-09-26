<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);

$aData = App()->getController()->aData;
// print_r(App()->getController())

$layoutHelper = new LayoutHelper();

//All paths relative from /application/views

$layoutHelper->showHeaders($aData, false);

$layoutHelper->showadminmenu($aData);

echo "<!-- BEGIN LAYOUT_INSURVEY -->";
// Needed to evaluate EM expressions in question summary
// See bug #11845
LimeExpressionManager::StartProcessingPage(false, true);
$aData['debug'] = $aData;

echo $layoutHelper->renderTopbarTemplate($aData);

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

// echo "<pre>".print_r($aData, true)."</pre>";

//The container to hold the vuejs application
echo ' <!-- Survey page, started in SurveyCommonAction::renderWrappedTemplate() -->
        <div id="vue-apps-main-container" '
    . 'class="ls-flex-row align-items-flex-begin align-content-flex-end col-12 container-fluid pb-6" '
    . '>';

$layoutHelper->renderSurveySidemenu($aData);

echo '<div '
    . 'class="ls-flex-column align-items-flex-start align-content-flex-start col-11 ls-flex-item transition-animate-width main-content-container" '
    . '>';

echo '<div id="pjax-content" class="col-12 mb-5">';

echo '<div id="in_survey_common" '
    . 'class="container-fluid pe-0 ls-flex-column col-12"'
    . '>';

//Rendered through /admin/update/_update_notification
$layoutHelper->updatenotification();
$layoutHelper->notifications();

echo $content;

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<!-- END LAYOUT_INSURVEY -->";

// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    //Yii::app()->getController()->_loadEndScripts();
    $layoutHelper->loadEndScripts();
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        //Yii::app()->getController()->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
        $layoutHelper->getAdminFooter('http://manual.limesurvey.org', false, true);
    }
} else {
    echo '</body>
    </html>';
}
