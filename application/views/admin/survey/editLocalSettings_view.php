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

<div id="edittxtele-<?php echo $i;?>" class="tab-pane fade in <?php if($i==0){echo "active";}?> center-box">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <!-- Survey title -->
                <div class="form-group">
                    <label class=" question-group-title control-label" for="short_title_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>">
                        <?php eT("Survey title:"); ?>
                    </label>
                    <div class="">
                        <?php echo CHtml::textField("short_title_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_title'],array('class'=>'form-control','size'=>"80",'id'=>"short_title_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>
            </div>
            <hr class="col-sm-12" />
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <!-- Description -->
                <div class="form-group">
                    <label class=" control-label"  for="description_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>"><?php eT("Description:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("description_{$aSurveyLanguageSettings['surveyls_language']}", $aSurveyLanguageSettings['surveyls_description'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"description_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-desc","description_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Description:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-6">
                <!-- End URL -->
                <div class="form-group">
                    <label class="control-label "><?php eT("End URL:"); ?></label>
                    <div class="">
                        <?php echo CHtml::textField("url_{$aSurveyLanguageSettings['surveyls_language']}", $aSurveyLanguageSettings['surveyls_url'],array('class'=>'form-control','size'=>"80",'placeholder'=>'http://','id'=>"url_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>

                <!-- URL description -->
                <div class="form-group">
                    <label class="control-label "><?php eT("URL description:"); ?></label>
                    <div class="">
                        <?php echo CHtml::textField("urldescrip_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_urldescription'],array('class'=>'form-control','size'=>"80",'id'=>"urldescrip_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>

                <!-- Date format -->
                <div class="form-group">
                    <label class="control-label "><?php eT("Date format:"); ?></label>

                    <div class="">
                        <select size='1' id='dateformat_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>' name='dateformat_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>' class="form-control">
                            <?php foreach (getDateFormatData(0,Yii::app()->session['adminlang']) as $index=>$dateformatdata): ?>
                                <option value='<?php echo $index; ?>'
                                <?php if ($aSurveyLanguageSettings['surveyls_dateformat']==$index): ?>
                                    selected='selected'
                                <?php endif; ?>
                                ><?php echo $dateformatdata['dateformat']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Decimal mark -->
                <div class="form-group">
                    <label class="control-label "><?php eT("Decimal mark:"); ?></label>
                    <div class="">
                        <?php
                            $aRadixPoint=array();
                            foreach (getRadixPointData() as $index=>$radixptdata)
                            {
                                $aRadixPoint[$index]=html_entity_decode($radixptdata['desc']);
                            }
                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'numberformat_'.$aSurveyLanguageSettings['surveyls_language'],
                            'value'=> $aSurveyLanguageSettings['surveyls_numberformat'] ,
                            'selectOptions'=>$aRadixPoint,
                            'htmlOptions' => array(
                                "style" => "z-index:0"
                            )
                            ));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <!-- Welcome message -->
                <div class="form-group">
                    <label class=" control-label" for='welcome_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("Welcome message:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("welcome_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_welcometext'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"welcome_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-welc","welcome_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Welcome:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-6">
                <!-- End message -->
                <div class="form-group">
                    <label class=" control-label" for='endtext_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("End message:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("endtext_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_endtext'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"endtext_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-endtext","endtext_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("End message:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <!-- Data security checkbox label -->
                <div class="form-group">
                    <label class="control-label"><?php eT("Data security checkbox label:"); ?> 
                    <i class="fa fa-question-circle" id="dataseclabel_popover_<?=$aSurveyLanguageSettings['surveyls_language']?>" data-toggle="popover" title="<?=gT('How to link to the survey policy statement modal window')?>"  data-content="<?php
                        eT("If you want to specify a link to the survey policy please use the placeholders {STARTPOLICYLINK} and {ENDPOLICYLINK} to define the link that opens the popup. If there is no placeholder given, there will be an appendix.")
                    ?>"></i> </label>
                    <div class="">
                        <?php echo CHtml::textField("dataseclabel_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_policy_notice_label'],array('class'=>'form-control','size'=>"80",'id'=>"dataseclabel_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>
                <!-- Data security message -->
                <div class="form-group">
                    <label class=" control-label" for='datasec_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("Data security message:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("datasec_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_policy_notice'],array('class'=>'form-control','cols'=>'80','rows'=>'20','id'=>"datasec_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-datasec","datasec_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Data security:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-6">
                <!-- Data security error message -->
                <div class="form-group">
                    <label class=" control-label" for='datasecerror_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("Data security error message:"); ?></label>
                    <div class="">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("datasecerror_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_policy_error'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"datasecerror_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-datasec-error","datasecerror_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Data security error:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
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
