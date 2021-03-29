<?php
/**
 * Tokens panel
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyParticipantTokenOptions');

App()->getClientScript()->registerScript("tokens-panel-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using participants or notification emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
    
    function alertPrivacy()
    {
        if ($('#tokenanswerspersistence').is(':checked') == true) {
            $('#alertPrivacy1').modal();
            document.getElementById('anonymized').value = '0';
        }
        else if ($('#anonymized').is(':checked') == true) {
            $('#alertPrivacy2').modal();
        }
    }
    
", LSYii_ClientScript::POS_BEGIN);
?>

<!-- tokens panel -->
<div id='tokens-panel' class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <!--  Set token length to -->
            <div class="form-group">
                <?php $tokenlength = $oSurvey->tokenlength; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                        <label class=" control-label"  for='tokenlength'><?php  eT("Set access code length to:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $tokenlength === '-1' ? 'hide' : 'show'); ?>" type='text' size='50' id='tokenlength' name='tokenlength' value="<?php echo htmlspecialchars($tokenlength); ?>" data-inherit-value="-1" data-saved-value="<?php echo $tokenlength; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $tokenlength === '-1' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->tokenlength); ?>" readonly />
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                        <label class=" control-label content-center col-sm-12"  for='tokenlength'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'tokenlengthbutton',
                            'value'=> ($bShowInherited && $tokenlength === '-1' ? 'Y' : 'N'),
                            'selectOptions'=>$optionsOnOff,
                            'htmlOptions' => array(
                                'class' => 'text-option-inherit'
                                )
                            ));
                            ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <!-- Anonymized responses -->
            <div class="form-group">
                <label  class=" control-label"  for='anonymized' title='<?php eT("If you set 'Yes' then no link will exist between survey participants table and survey responses table. You won't be able to identify responses by their access code."); ?>'>
                    <?php  eT("Anonymized responses:"); ?>
                </label>
                <div class="">
                    <?php if ($oSurvey->isActive) {
                        if ($oSurvey->anonymized == "N") { ?>
                        <?php  eT("Responses to this survey are NOT anonymized."); ?>
                        <?php } else {
                            eT("Responses to this survey are anonymized.");
                    } ?>
                    <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                    <input type='hidden' id='anonymized' name='anonymized' value="<?php echo $oSurvey->anonymized; ?>" />
                    <?php } else {

                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'anonymized',
                            'value'=> $oSurvey->anonymized,
                            'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->anonymized . ']')): $optionsOnOff,
                            /*'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                                alertPrivacy();
                            }")*/
                            ));
                        }?>
                </div>
            </div>

            <!-- Enable token-based response persistence -->
            <div class="form-group">
                <label class=" control-label" for='tokenanswerspersistence' title='<?php  eT("With non-anonymized responses (and the survey participants table field 'Uses left' set to 1) if the participant closes the survey and opens it again (by using the survey link) his previous answers will be reloaded."); ?>'>
                    <?php  eT("Enable participant-based response persistence:"); ?>
                </label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'tokenanswerspersistence',
                    'value'=> $oSurvey->tokenanswerspersistence,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->tokenanswerspersistence . ']')): $optionsOnOff,
                    /*'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                        if ($('#anonymized').is(':checked') == true) {
                        $('#tokenanswerspersistenceModal').modal();
                        }
                    }")*/
                    ));
                    /*$this->widget('bootstrap.widgets.TbModal', array(
                        'id' => 'tokenanswerspersistenceModal',
                        'header' => gt('Error','unescaped'),
                        'content' => '<p>'.gT("This option can't be used if the -Anonymized responses- option is active.").'</p>',
                        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
                    ));*/
                ?>
                </div>
            </div>

            <!-- Allow multiple responses or update responses with one token -->
            <div class="form-group">
                <label class=" control-label" for='alloweditaftercompletion' title='<?php  eT("If participant-based response persistence is enabled a participant can update his response after completion, else a participant can add new responses without restriction."); ?>'>
                    <?php  eT("Allow multiple responses or update responses with one access code:"); ?>
                </label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'alloweditaftercompletion',
                        'value'=> $oSurvey->alloweditaftercompletion,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->alloweditaftercompletion . ']')): $optionsOnOff
                    ));
                ?>
                </div>
            </div>

            <!-- Allow public registration -->
            <div class="form-group">
                <label class=" control-label" for='allowregister'><?php  eT("Allow public registration:"); ?></label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'allowregister',
                        'value'=> $oSurvey->allowregister,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->allowregister . ']')): $optionsOnOff
                    ));
                ?>
                </div>
            </div>

            <!-- Use HTML format for token emails -->
            <div class="form-group">
                <label class=" control-label" for='htmlemail'><?php  eT("Use HTML format for participant emails:"); ?></label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'htmlemail',
                    'value'=> $oSurvey->htmlemail,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->htmlemail . ']')): $optionsOnOff,
                    /*'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                        $('#htmlemailModal').modal();
                    }")*/
                    ));
                    $this->widget('bootstrap.widgets.TbModal', array(
                        'id' => 'htmlemailModal',
                        'header' => gt('Error','unescaped'),
                        'content' => '<p>'.gT("If you change the email format, you'll have to review your email templates to fit the new format").'</p>',
                        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
                    ));
                    ?>
                </div>
            </div>

            <!-- Send confirmation emails -->
            <div class="form-group">
                <label class=" control-label" for='sendconfirmation'><?php  eT("Send confirmation emails:"); ?></label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'sendconfirmation',
                        'value'=> $oSurvey->sendconfirmation,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->sendconfirmation . ']')): $optionsOnOff
                    ));
                ?>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
    $this->widget('bootstrap.widgets.TbModal', array(
        'id' => 'alertPrivacy1',
        'header' => gt('Warning','unescaped'),                    
        'content' => '<p>'.gT("You can't use 'Anonymized responses' when participant-based response persistence is enabled.").'</p>',
        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
    ));
    $this->widget('bootstrap.widgets.TbModal', array(
        'id' => 'alertPrivacy2',
        'header' => gt('Warning','unescaped'),
        'content' => '<p>'.gT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.").'</p>',
        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
    ));
    ?>
