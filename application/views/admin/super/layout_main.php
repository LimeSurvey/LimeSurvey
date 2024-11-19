<?php

//All paths relative from /application/views
/**
 * @var SurveyCommonAction $this
 * @var array $aData
 *
 * todo: remove this view when all controllers are refactored
 */

//headers will be generated with the template file /admin/super/header.php
$this->showHeaders($aData);

//The adminmenu bar will be generated from /admin/super/adminmenu.php
$this->showadminmenu($aData);

$layoutHelper = new LayoutHelper();

echo $layoutHelper->renderTopbarTemplate($aData);
echo "<!-- BEGIN LAYOUT_MAIN -->";

echo "<div class='container-fluid'>\n";
$this->updatenotification();
echo "</div>\n";

$this->notifications();

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

$containerClass = !Yii::app()->user->isGuest ? 'container-fluid full-page-wrapper' : 'container-fluid ps-0';
echo '<!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
<div class="' . $containerClass . '" id="in_survey_common_action">';

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
