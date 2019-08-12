<?php
/**
 * Survey default view
 * @var AdminController $this
 * @var Survey $oSurvey
 */
 $count= 0;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveySummary');

//TODO : move to controller
$templates = Template::getTemplateListWithPreviews();
//print_r($templates);
$count = 0;
$surveyid = $oSurvey->sid;
$templateModel = Template::model()->findByPk($oSurvey->oOptions->template);
     $surveylocale = Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read');
     // EDIT SURVEY SETTINGS BUTTON
     $surveysettings = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read');
     $respstatsread = Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')
         || Permission::model()->hasSurveyPermission($iSurveyID, 'statistics', 'read')
         || Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export');



?>
<!-- START surveySummary -->
<div class="row">
    <div class="col-sm-12 h3 pagetitle">
        <?php eT('Survey summary'); ?> :
        <?php echo flattenText($oSurvey->currentLanguageSettings->surveyls_title)." (".gT("ID")." ".$oSurvey->sid.")";?>
    </div>
</div>
<?php /*
/// Survey quick actions have been removed -> deprecated
<div class="row">
    <div class="col-sm-12">
        <?php echo $this->renderPartial('/admin/survey/subview/_survey_quickaction', $subviewData); ?>    
    </div>
</div>
*/ ?>
<div class="row ls-space margin top-10">
<?php
    $possiblePanelFolder = realpath(Yii::app()->getConfig('rootdir').'/application/views/admin/survey/subview/surveydashboard/'); 
    $possiblePanels = scandir($possiblePanelFolder); 
    foreach ($possiblePanels as $i => $panel) {
         
        // If it's no twig file => ignore 
        if(!preg_match('/^.*\.twig$/',$panel)){  
            continue;  
        } 
        //every two entries close it up 
        if($i%2 === 0 ) { ?> 
            </div> 
            <div class="row ls-space margin top-10">
        <?php } ?> 
        <div class="col-md-12 col-lg-6"> 
            <?php $surveyTextContent = $oSurvey->currentLanguageSettings->attributes; ?>
        <?=App()->twigRenderer->renderViewFromFile('/application/views/admin/survey/subview/surveydashboard/'.$panel, get_defined_vars(), true)?>
        </div> 
    <?php }
?> 
</div>
<!-- END surveySummary -->
