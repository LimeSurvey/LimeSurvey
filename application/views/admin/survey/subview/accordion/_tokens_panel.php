<?php
/**
 * Tokens panel
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyParticipantTokenOptions');

App()->getClientScript()->registerScript("tokens-panel-variables",
    "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '" . gT("If you are using participants or notification emails you need to set an administrator email address.",
        'js') . "'
    var sURLParameters = '';
    var sAddParam = '';
    
    
    function alertPrivacy()
    {
        if ($('#tokenanswerspersistence_opt1').is(':checked') == true) {
            $('#alertPrivacy1').modal();
            document.getElementById('anonymized').value = '0';
        }
        else if ($('#anonymized_1').is(':checked') == true) {
            $('#alertPrivacy2').modal();
        }
    }
    window.addEventListener('load', (event) => {
        document.getElementById('anonymized').addEventListener('change', (event) => {
            alertPrivacy();
        });
        document.getElementById('htmlemail').addEventListener('change', (event) => {
            $('#htmlemailModal').modal();
        });
    });
    
",
    LSYii_ClientScript::POS_BEGIN);
?>

<!-- tokens panel -->
<div id='tokens-panel'>
    <div class="row">
        <div class="col-12 col-lg-6">
            <!--  Set token length to -->
            <div class="form-group">
                <?php $tokenlength = $oSurvey->tokenlength; ?>
                <div class="row">
                    <div class="col-12 col-lg-8 content-right">
                        <label class=" form-label"  for='tokenlength'><?php  eT("Set access code length to:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $tokenlength == '-1' ? 'd-none' : 'd-block'); ?>" type='text' size='50' id='tokenlength' name='tokenlength' value="<?php echo htmlspecialchars($tokenlength); ?>" data-inherit-value="-1" data-saved-value="<?php echo $tokenlength; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $tokenlength == '-1' ? 'd-block' : 'd-none'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->tokenlength); ?>" readonly />
                    </div>
                    <div class="col-12 col-lg-4 content-right <?php echo ($bShowInherited ? 'd-block' : 'd-none'); ?>">
                        <label class=" form-label content-center col-12"  for='tokenlength'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'tokenlengthbutton',
                            'checkedOption' => ($bShowInherited && $tokenlength == '-1' ? 'Y' : 'N'),
                            'selectOptions' => $optionsOnOff,
                            'htmlOptions'   => [
                                'class' => 'text-option-inherit'
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <!-- Anonymized responses -->
            <div class="form-group">
                <label  class=" form-label"  for='anonymized' title='<?php eT("If you set 'Yes' then no link will exist between survey participants table and survey responses table. You won't be able to identify responses by their access code."); ?>'>
                    <?php  eT("Anonymized responses:"); ?>
                </label>
                <div>
                    <?php if ($oSurvey->isActive) {
                        if ($oSurvey->anonymized == "N") { ?>
                            <?php eT("Responses to this survey are NOT anonymized."); ?>
                        <?php } else {
                            eT("Responses to this survey are anonymized.");
                        } ?>
                        <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                        <input type='hidden' id='anonymized' name='anonymized' value="<?php echo $oSurvey->anonymized; ?>"/>
                    <?php } else {
                        $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name' => 'anonymized',
                            'checkedOption' => $oSurvey->anonymized,
                            'selectOptions' => ($bShowInherited) ? array_merge($optionsOnOff,
                                ['I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->anonymized . ']']) : $optionsOnOff,
                        ]);
                    } ?>
                </div>
            </div>

            <!-- Enable token-based response persistence -->
            <div class="form-group">
                <label class=" form-label" for='tokenanswerspersistence' title='<?php  eT("With non-anonymized responses (and the survey participants table field 'Uses left' set to 1) if the participant closes the survey and opens it again (by using the survey link) his previous answers will be reloaded."); ?>'>
                    <?php  eT("Enable participant-based response persistence:"); ?>
                </label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'tokenanswerspersistence',
                        'checkedOption' => $oSurvey->tokenanswerspersistence,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->tokenanswerspersistence . ']'])
                            : $optionsOnOff
                    ]) ?>
                </div>
            </div>

            <!-- Allow multiple responses or update responses with one token -->
            <div class="form-group">
                <label class=" form-label" for='alloweditaftercompletion' title='<?php  eT("If participant-based response persistence is enabled a participant can update his response after completion, else a participant can add new responses without restriction."); ?>'>
                    <?php  eT("Allow multiple responses or update responses with one access code:"); ?>
                </label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'alloweditaftercompletion',
                        'checkedOption' => $oSurvey->alloweditaftercompletion,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->alloweditaftercompletion . ']'])
                            : $optionsOnOff
                    ]); ?>
                </div>
            </div>

            <!-- Allow public registration -->
            <div class="form-group">
                <label class=" form-label" for='allowregister'><?php eT("Allow public registration:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'allowregister',
                        'checkedOption' => $oSurvey->allowregister,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->allowregister . ']'])
                            : $optionsOnOff
                    ]); ?>
                </div>
            </div>

            <!-- Use HTML format for token emails -->
            <div class="form-group">
                <label class=" form-label" for='htmlemail'><?php  eT("Use HTML format for participant emails:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'htmlemail',
                        'checkedOption' => $oSurvey->htmlemail,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->htmlemail . ']'])
                            : $optionsOnOff,
                    ]);
                    $this->widget('yiistrap.widgets.TbModal', [
                        'id'      => 'htmlemailModal',
                        'header'  => gT('Error', 'unescaped'),
                        'content' => '<p>' . gT("If you change the email format, you'll have to review your email templates to fit the new format") . '</p>',
                        'footer'  => TbHtml::button('Close', ['data-dismiss' => 'modal'])
                    ]); ?>
                </div>
            </div>

            <!-- Send confirmation emails -->
            <div class="form-group">
                <label class=" form-label" for='sendconfirmation'><?php eT("Send confirmation emails:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name' => 'sendconfirmation',
                        'checkedOption' => $oSurvey->sendconfirmation,
                        'selectOptions' => ($bShowInherited) ? array_merge($optionsOnOff,
                            ['I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->sendconfirmation . ']']) : $optionsOnOff
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
    $this->widget('yiistrap.widgets.TbModal', array(
        'id' => 'alertPrivacy1',
        'header' => gT('Warning','unescaped'),                    
        'content' => '<p>'.gT("You can't use 'Anonymized responses' when participant-based response persistence is enabled.").'</p>',
        'footer' => TbHtml::button('Close', array('data-bs-dismiss' => 'modal'))
    ));
    $this->widget('yiistrap.widgets.TbModal', array(
        'id' => 'alertPrivacy2',
        'header' => gT('Warning','unescaped'),
        'content' => '<p>'.gT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.").'</p>',
        'footer' => TbHtml::button('Close', array('data-bs-dismiss' => 'modal'))
    ));
    ?>
