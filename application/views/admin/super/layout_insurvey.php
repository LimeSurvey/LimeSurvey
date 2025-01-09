<?php

/* @deprecated moved to layouts/layout_questioneditor */
//todo should be removed when all controllers have been refactored

    //All paths relative from /application/views

$layoutHelper = new LayoutHelper();

//headers will be generated with the template file /admin/super/header.php
$this->showHeaders($aData, false);
//The adminmenu bar will be generated from /admin/super/adminmenu.php
$this->showadminmenu($aData);

// Generated through /admin/usergroup/usergroupbar_view
//$this->userGroupBar($aData);

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
. 'class="ls-flex-row align-items-flex-begin align-content-flex-end col-12 pb-6" '
. '>';
    //Rendered through /admin/super/sidemenu
    $layoutHelper->renderSurveySidemenu($aData);
    echo '<div '
    . 'class="ls-flex-column align-items-flex-start align-content-flex-start col-11 ls-flex-item transition-animate-width main-content-container" '
    . '>';

        echo '<div id="pjax-content" class="col-12">';

            //Rendered through /admin/responses/browsemenubar_view
            //$this->_browsemenubar($aData);
            //Rendered through /admin/survey/Question/questionbar_view
            $this->organizequestionbar($aData);

            echo '<div id="in_survey_common" '
            . 'class="container-fluid ls-flex-column col-12"'
            . '>';

                //Rendered through /admin/update/_update_notification
                $this->updatenotification();
                //Rendered through /notifications/notifications
                $this->notifications();

                //// Here the main content views.
                //Rendered through /admin/survey/QuestionGroups/listquestiongroups
                $this->listquestiongroups($aData);
                //Rendered through /admin/survey/Question/listquestions
                $this->listquestions($aData);
                //Rendered through /admin/super/sidebody
                //With content of /admin/survey/surveySummary_view
                $this->nsurveysummary($aData);

                echo $content;

            echo "</div>\n";
        echo "</div>\n";
    echo "</div>\n";
echo "</div>\n";
echo "<!-- END LAYOUT_INSURVEY -->";

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
