<?php

/**
 * Survey default view
 *
 * @var SurveyAdministrationController $this
 * @var Survey $oSurvey
 */
$count = 0;

if (!isset($iSurveyID)) {
    $iSurveyID = $oSurvey->sid;
}

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveySummary');

$count         = 0;
$surveyid      = $oSurvey->sid;
$templateModel = Template::model()->findByPk($oSurvey->oOptions->template)->getAttributes();
$surveylocale  = Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read');
// EDIT SURVEY SETTINGS BUTTON
$surveysettings = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read');
$respstatsread  = Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')
    || Permission::model()->hasSurveyPermission($iSurveyID, 'statistics', 'read')
    || Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export');
$bSurveyIsListPublic = $oSurvey->getIsListPublic()


?>
<!-- START surveySummary -->
<!-- <div class="row">
    <div class="col-12">
        <div class="h3 pagetitle">
            <?php eT('Survey summary'); ?> :
            <?php echo flattenText($oSurvey->currentLanguageSettings->surveyls_title) . " (" . gT("ID") . " " . $oSurvey->sid . ")"; ?>
        </div>
    </div>
</div> -->
<div class="ls-card-grid">
<?php
    //survey has been activated in open-access mode
   if (isset($surveyActivationFeedback)) {
       $this->renderPartial('/surveyAdministration/surveyActivation/_feedbackOpenAccess', ['surveyId' => $iSurveyID]);
   }
?>
<div class="row survey-summary mt-4">
        <?php
        $possiblePanelFolder = realpath(Yii::app()->getConfig('rootdir') . '/application/views/admin/survey/subview/surveydashboard/');
        $possiblePanels = scandir($possiblePanelFolder);
        foreach ($possiblePanels as $i => $panel) {
        // If it's no twig file => ignore
        if (!preg_match('/^.*\.twig$/', (string)$panel)) {
            continue;
        }
        //every two entries close it up
        if ($i % 2 === 0) { ?>
    </div>
<div class="row survey-summary mt-4">
        <?php } ?>
        <div class="col-12 col-xl-6 mb-4">
            <?php $surveyTextContent = $oSurvey->currentLanguageSettings->attributes; ?>
            <?= App()->twigRenderer->renderViewFromFile('/application/views/admin/survey/subview/surveydashboard/' . $panel, get_defined_vars(), true) ?>
        </div>
        <?php }
        ?>
    </div>
</div>
<!-- END surveySummary -->
