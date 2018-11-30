<?php
/**
 * Edit the survey text elements of a survey for one given language
 * It is rendered from editLocalSettings_main_view.
 *
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTexts');

?>

<?php App()->getClientScript()->registerScript("editLocalSettings-view-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN); ?>

<div id="editdatasecele-<?php echo $i;?>" class="tab-pane fade in <?php if($i==0){echo "active";}?> center-box">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <!-- Survey data policy checkbox label -->
                <div class="form-group">
                    <label class="control-label"><?php eT("Survey data policy checkbox label:"); ?></label>
                    <div class="">
                        <?php echo CHtml::textField("dataseclabel_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_policy_notice_label'],array('class'=>'form-control','size'=>"80",'id'=>"dataseclabel_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-6">
                <div class="well">
                    <?=gT('If you want to specify a link to the survey data policy, set "Show survey policy text with mandatory checkbox" to "Collapsible text" and use the placeholders {STARTPOLICYLINK} and {ENDPOLICYLINK} in the "Survey data policy checkbox label" field to define the link that opens the policy popup. If there is no placeholder given, there will be an appendix.')?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <!-- Survey data policy message -->
                <div class="form-group">
                    <label class=" control-label" for='datasec_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("Survey data policy message:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("datasec_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_policy_notice'],array('class'=>'form-control','cols'=>'80','rows'=>'20','id'=>"datasec_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-datasec","datasec_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Survey data policy:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-6">
                <!-- Survey data policy error message -->
                <div class="form-group">
                    <label class=" control-label" for='datasecerror_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("Survey data policy error message:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("datasecerror_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_policy_error'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"datasecerror_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-datasec-error","datasecerror_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Survey data policy error:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
App()->getClientScript()->registerScript(
    'popover_'.$aSurveyLanguageSettings['surveyls_language'], 
    '$("dataseclabel_popover_'.$aSurveyLanguageSettings['surveyls_language'].'").popover()', 
    LSYii_ClientScript::POS_POSTSCRIPT 
)
?>
