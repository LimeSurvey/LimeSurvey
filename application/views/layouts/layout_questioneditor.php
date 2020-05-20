<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);

$aData = Yii::app()->getController()->aData;

$layoutHelper = new LayoutHelper();

//All paths relative from /application/views

$layoutHelper->showHeaders($aData, false);
//$this->_showHeaders($aData, false);

//$this->_showadminmenu($aData);
$layoutHelper->showadminmenu($aData);

echo "<!-- BEGIN LAYOUT_INSURVEY -->";
// Needed to evaluate EM expressions in question summary
// See bug #11845
LimeExpressionManager::StartProcessingPage(false, true);
$aData['debug'] = $aData;
//$this->_titlebar($aData);
$layoutHelper->rendertitlebar($aData);

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

// echo "<pre>".print_r($aData, true)."</pre>";

//The container to hold the vuejs application
echo ' <!-- Survey page, started in Survey_Common_Action::render_wrapped_template() -->
        <div id="vue-apps-main-container" '
    . 'class="ls-flex-row align-items-flex-begin align-content-flex-end col-12" '
    . '>';

//Rendered through /admin/super/sidemenu
//$this->_surveysidemenu($aData);
$layoutHelper->renderSurveySidemenu($aData);


echo '<div '
    . 'class="ls-flex-column align-items-flex-start align-content-flex-start col-11 ls-flex-item transition-animate-width main-content-container" '
    . '>';
//New general top bar (VueComponent)
//$this->_generaltopbar($aData);
$layoutHelper->renderGeneraltopbar($aData);

echo '<div id="pjax-content" class="col-12">';

//Rendered through /admin/responses/browsemenubar_view
//$this->_browsemenubar($aData);
//Rendered through /admin/survey/Question/questionbar_view
//$this->_organizequestionbar($aData);
$layoutHelper->renderOrganizeQuestionBar($aData);

echo '<div id="in_survey_common" '
    . 'class="container-fluid ls-flex-column fill col-12"'
    . '>';

//Rendered through /admin/update/_update_notification
//$this->_updatenotification();
$layoutHelper->updatenotification();

//Rendered through /notifications/notifications
//$this->_notifications();
$layoutHelper->notifications();

//// Here the main content views.
//Rendered through /admin/survey/QuestionGroups/listquestiongroups
//$this->_listquestiongroups($aData);
$layoutHelper->renderListQuestionGroups($aData);

//Rendered through /admin/survey/Question/listquestions
//$this->_listquestions($aData);
$layoutHelper->renderListQuestions($aData);


//Rendered through /admin/super/sidebody
//With content of /admin/survey/surveySummary_view
//$this->_nsurveysummary($aData);
$layoutHelper->renderSurveySummary($aData);


echo $content;

//$this->_generaltopbarAdditions($aData);
$layoutHelper->renderGeneralTopbarAdditions($aData);
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
        $layoutHelper->getAdminFooter('http://manual.limesurvey.org');
    }
} else {
    echo '</body>
    </html>';
}
