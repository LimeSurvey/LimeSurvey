<?php
/**
 * Edit the survey text elements of a survey for one given language
 * It is rendered from editLocalSettings_main_view.
 */
?>
<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php  eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>'
    var sURLParameters = '';
    var sAddParam = '';
</script>
<div id="edittxtele-<?php echo $i;?>" class="tab-pane fade in <?php if($i==0){echo "active";}?> center-box">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <!-- Survey title -->
                <div class="form-group">
                    <label class="col-sm-2 question-group-title control-label" for="short_title_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>">
                        <?php eT("Survey title:"); ?>
                    </label>
                    <div class="col-sm-9">
                        <?php echo CHtml::textField("short_title_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_title'],array('class'=>'form-control','size'=>"80",'id'=>"short_title_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="col-sm-2 control-label"  for="description_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>"><?php eT("Description:"); ?></label>
                    <div class="col-sm-9">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("description_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_description'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"description_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-desc","description_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Description:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>

                <!-- Welcome message -->
                <div class="form-group">
                    <label class="col-sm-2 control-label" for='welcome_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("Welcome message:"); ?></label>
                    <div class="col-sm-9">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("welcome_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_welcometext'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"welcome_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-welc","welcome_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("Welcome:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <!-- End message -->
                <div class="form-group">
                    <label class="col-sm-2 control-label" for='endtext_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'><?php eT("End message:"); ?></label>
                    <div class="col-sm-9">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("endtext_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_endtext'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"endtext_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                        <?php echo getEditor("survey-endtext","endtext_".$aSurveyLanguageSettings['surveyls_language'], "[".gT("End message:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                    </div>
                </div>
                <!-- End URL -->
                <div class="form-group">
                    <label class="control-label col-sm-2"><?php eT("End URL:"); ?></label>
                    <div class="col-sm-9">
                        <?php echo CHtml::textField("url_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_url'],array('class'=>'form-control','size'=>"80",'placeholder'=>'http://','id'=>"url_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>

                <!-- URL description -->
                <div class="form-group">
                    <label class="control-label col-sm-2"><?php eT("URL description:"); ?></label>
                    <div class="col-sm-9">
                        <?php echo CHtml::textField("urldescrip_{$aSurveyLanguageSettings['surveyls_language']}",$aSurveyLanguageSettings['surveyls_urldescription'],array('class'=>'form-control','size'=>"80",'id'=>"urldescrip_{$aSurveyLanguageSettings['surveyls_language']}")); ?>
                    </div>
                </div>

                <!-- Date format -->
                <div class="form-group">
                    <label class="control-label col-sm-2"><?php eT("Date format:"); ?></label>

                    <div class="col-sm-3">
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
                    <label class="control-label col-sm-2"><?php eT("Decimal mark:"); ?></label>
                    <div class="col-sm-3">
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
    </div>
</div>
