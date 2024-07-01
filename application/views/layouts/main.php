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

echo "<!-- BEGIN LAYOUT MAIN (refactored controllers-->";

echo $layoutHelper->renderTopbarTemplate($aData);

echo "<div class='container-fluid'>";
$layoutHelper->updatenotification();
echo "</div>";

$layoutHelper->notifications();

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

echo '<!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
      <div class="full-page-wrapper container-fluid" id="in_survey_common_action">';

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
