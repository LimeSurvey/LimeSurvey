<?php
/**
* General container for create survey action
 * @var AdminController $this
 * @var Survey $oSurvey
*/
?>

  <script type="text/javascript">
    var standardtemplaterooturl = '<?php echo Yii::app()->getConfig('
    standardtemplaterooturl ');?>';
    var templaterooturl = '<?php echo Yii::app()->getConfig('
    usertemplaterooturl ');?>';
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php  eT("If you are using token functions or notifications emails you need to set an administrator email address.",'
    js '); ?>'
    var sURLParameters = '';
    var sAddParam = '';
  </script>

<?php
$count = 0;
if(isset($scripts))
echo $scripts;

$aSurveyLanguageSettings = $aTabContents['aSurveyLanguageSettings'];
$surveyid = $aTabContents['surveyid'];
?>


<!-- Edition container -->
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <!-- Survey title -->
            <div class="form-group">
                <label class="col-sm-2 question-group-title control-label" for="surveyls_title">
                    <?php eT("Survey title:"); ?>
                </label>
                <div class="col-sm-9">
                    <?php echo CHtml::textField("surveyls_title",$aSurveyLanguageSettings['surveyls_title'],array('class'=>'form-control','size'=>"80",'id'=>"surveyls_title")); ?>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
             <div class="form-group">
                <label for="createsample"><?php eT("Create example question and question group?") ?></label>
                <!--<input type="checkbox" name="createsample" id="createsample" />-->
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'createsample',
                        'value' => 1,
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                    )); ?>
            </div>
        </div>
        <hr class="col-sm-12"></hr>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <!-- Description -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="description">
                    <?php eT("Description:"); ?>
                </label>
                <div class="col-sm-9">
                    <div class="htmleditor input-group">
                        <?php echo CHtml::textArea("description",$aSurveyLanguageSettings['surveyls_description'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"description")); ?>
                        <?php echo getEditor("survey-desc","description", "[".gT("Description:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <!-- End URL -->
            <div class="form-group">
                <label class="control-label col-sm-2">
                    <?php eT("End URL:"); ?>
                </label>
                <div class="col-sm-9">
                    <?php echo CHtml::textField("url",$aSurveyLanguageSettings['surveyls_url'],array('class'=>'form-control','size'=>"80",'placeholder'=>'http://','id'=>"url")); ?>
                </div>
            </div>

            <!-- URL description -->
            <div class="form-group">
                <label class="control-label col-sm-2">
                    <?php eT("URL description:"); ?>
                </label>
                <div class="col-sm-9">
                    <?php echo CHtml::textField("urldescrip",$aSurveyLanguageSettings['surveyls_urldescription'],array('class'=>'form-control','size'=>"80",'id'=>"urldescrip")); ?>
                </div>
            </div>

            <!-- Date format -->
            <div class="form-group">
                <label class="control-label col-sm-2">
                    <?php eT("Date format:"); ?>
                </label>

                <div class="col-sm-3">
                    <select size='1' id='dateformat' name='dateformat' class="form-control">
                    <?php foreach (getDateFormatData(0,Yii::app()->session['adminlang']) as $index=>$dateformatdata): ?>
                        <option value='<?php echo $index; ?>' <?php if ($aSurveyLanguageSettings[ 'surveyls_dateformat']==$index): ?>
                        selected='selected'
                        <?php endif; ?>
                            >
                            <?php echo $dateformatdata['dateformat']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Decimal mark -->
            <div class="form-group">
                <label class="control-label col-sm-2">
                    <?php eT("Decimal mark:"); ?>
                </label>
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
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <!-- Welcome message -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for='welcome'>
                    <?php eT("Welcome message:"); ?>
                </label>
                <div class="col-sm-9">
                    <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("welcome",$aSurveyLanguageSettings['surveyls_welcometext'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"welcome")); ?>
                        <?php echo getEditor("survey-welc","welcome", "[".gT("Welcome:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <!-- End message -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for='endtext'>
                    <?php eT("End message:"); ?>
                </label>
                <div class="col-sm-9">
                    <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("endtext",$aSurveyLanguageSettings['surveyls_endtext'],array('class'=>'form-control','cols'=>'80','rows'=>'15','id'=>"endtext")); ?>
                        <?php echo getEditor("survey-endtext","endtext", "[".gT("End message:", "js")."](".$aSurveyLanguageSettings['surveyls_language'].")",$surveyid,'','',$action); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#surveyls_title').focus();
    })
</script>