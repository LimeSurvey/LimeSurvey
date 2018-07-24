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
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
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
                <label class=" control-label" for='tokenlength'><?php  eT("Set token length to:"); ?></label>
                <div class="">
                    <input type='text' value="<?php echo $oSurvey->tokenlength ?>" name='tokenlength' id='tokenlength' size='4' maxlength='2' onkeypress="returnwindow.LS.goodchars(event,'0123456789')"  class="form-control" />
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <!-- Anonymized responses -->
            <div class="form-group">
                <label  class=" control-label"  for='anonymized' title='<?php eT("If you set 'Yes' then no link will exist between survey participants table and survey responses table. You won't be able to identify responses by their token."); ?>'>
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

                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'anonymized',
                            'value'=> $oSurvey->isAnonymized,
                            'onLabel'=>gT('On'),
                            'offLabel'=>gT('Off'),
                            'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                                alertPrivacy();
                            }")
                            ));
                        }?>
                </div>
            </div>

            <!-- Enable token-based response persistence -->
            <div class="form-group">
                <label class=" control-label" for='tokenanswerspersistence' title='<?php  eT("With non-anonymized responses (and the survey participants table field 'Uses left' set to 1) if the participant closes the survey and opens it again (by using the survey link) his previous answers will be reloaded."); ?>'>
                    <?php  eT("Enable token-based response persistence:"); ?>
                </label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'tokenanswerspersistence',
                    'value'=> $oSurvey->isTokenAnswersPersistence,
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off'),
                    'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                        if ($('#anonymized').is(':checked') == true) {
                        $('#tokenanswerspersistenceModal').modal();
                        }
                    }")
                    ));
                    $this->widget('bootstrap.widgets.TbModal', array(
                        'id' => 'tokenanswerspersistenceModal',
                        'header' => gt('Error','unescaped'),
                        'content' => '<p>'.gT("This option can't be used if the -Anonymized responses- option is active.").'</p>',
                        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
                    ));
                ?>
                </div>
            </div>

            <!-- Allow multiple responses or update responses with one token -->
            <div class="form-group">
                <label class=" control-label" for='alloweditaftercompletion' title='<?php  eT("If token-based response persistence is enabled a participant can update his response after completion, else a participant can add new responses without restriction."); ?>'>
                    <?php  eT("Allow multiple responses or update responses with one token:"); ?>
                </label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'alloweditaftercompletion',
                        'value'=> $oSurvey->isAllowEditAfterCompletion,
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                    ));
                ?>
                </div>
            </div>

            <!-- Allow public registration -->
            <div class="form-group">
                <label class=" control-label" for='allowregister'><?php  eT("Allow public registration:"); ?></label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'allowregister',
                        'value'=> $oSurvey->isAllowRegister,
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                    ));
                ?>
                </div>
            </div>

            <!-- Use HTML format for token emails -->
            <div class="form-group">
                <label class=" control-label" for='htmlemail'><?php  eT("Use HTML format for token emails:"); ?></label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'htmlemail',
                    'value'=> $oSurvey->isHtmlEmail,
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off'),
                    'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                        $('#htmlemailModal').modal();
                    }")
                    ));
                    $this->widget('bootstrap.widgets.TbModal', array(
                        'id' => 'htmlemailModal',
                        'header' => gt('Error','unescaped'),
                        'content' => '<p>'.gT("If you switch email mode, you'll have to review your email templates to fit the new format").'</p>',
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
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'sendconfirmation',
                        'value'=> $oSurvey->isSendConfirmation,
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
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
        'content' => '<p>'.gT("You can't use Anonymized responses when Token-based answers persistence is enabled.").'</p>',
        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
    ));
    $this->widget('bootstrap.widgets.TbModal', array(
        'id' => 'alertPrivacy2',
        'header' => gt('Warning','unescaped'),
        'content' => '<p>'.gT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.").'</p>',
        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
    ));
    ?>
