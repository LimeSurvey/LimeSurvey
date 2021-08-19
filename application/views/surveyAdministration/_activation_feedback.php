<?php
/**
* View for the message box after activated a survey
* It's called from SurveyAdmin::activate
*
* @var $iSurveyID
* @var $warning                    isset($aResult['warning'])
* @var $allowregister              $survey->allowregister=='Y'
* @var $onclickAction              convertGETtoPOST(Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/".$iSurveyID))
* @var $closedOnclickAction        convertGETtoPOST(Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/".$iSurveyID))
* @var $noOnclickAction            convertGETtoPOST(Yii::app()->getController()->createUrl("surveyAdministration/view/surveyid/".$iSurveyID))
*
*/
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-lg-12 content-right">
            <div class='jumbotron message-box'>
                <h3>
                    <?php eT('Activate Survey'); ?> (<?php echo $survey->currentLanguageSettings->surveyls_title; ?>)
                </h3>
                <p class='lead'>
                    <?php eT("Survey has been activated. Results table has been successfully created."); ?>
                </p>

                <?php if($warning):?>
                    <strong class='text-warning'>
                        <?php eT("The required directory for saving the uploaded files couldn't be created. Please check file premissions on the /upload/surveys directory."); ?>
                    </strong>
                    <?php endif; ?>

                <?php if($allowregister && !tableExists('tokens_'.$iSurveyID)):?>
                    <p>
                        <?php eT("This survey allows public registration. A survey participants table must also be created."); ?>
                        <br />
                        <br />
                        <input
                            type="submit"
                            class="btn btn-default btn-lg limebutton"
                            value="<?php eT("Initialise participant table"); ?>"
                            onclick='<?php echo $onclickAction;?>'
                            />
                    </p>
                    <?php else:?>
                    <p>
                        <?php eT("This survey is now active and responses can be recorded."); ?>
                        <br />
                        <br />
                        <?php if(!tableExists('tokens_'.$iSurveyID)):?>
                            <!-- Open Access Mode -->
                            <?php eT("By default, your survey has been activated in "); ?><strong><?php eT("open-access mode"); ?></strong>
                            <?php eT(". This means that no invitation code is needed to complete the survey and you can share it via URL, QR code or social media in the sharing panel."); ?>
                            <br />
                            <br />
                            <!-- Closed Access Mode -->
                            <?php eT("If you want your survey to be only accessible for people who have been invited, please switch to "); ?><strong><?php eT("closed-access mode"); ?></strong>
                            <?php eT("by clicking the following button."); ?>

                            <br />
                            <br />
                            <?php eT("If you change your mind at any later point, you can switch to closed-access mode by navigating ba to 'Survey participants' and initialising the participants table at any time.");?>
                            <br />
                            <br />
                            <input
                                type='submit'
                                class='btn btn-default'
                                id='activateTokenTable__selector--yes'
                                value='<?php eT("Switch to closed-access mode"); ?>'
                                />
                            <input
                                type='submit'
                                class='btn btn-default'
                                id='activateTokenTable__selector--no'
                                value='<?php eT("Continue in open-access mode"); ?>'
                                />
                            <?php else:?>
                            <input
                                type='submit'
                                class='btn btn-default'
                                id='activateRedirectSurvey__selector'
                                value='<?php eT("Back to survey home"); ?>'
                                />

                            <?php endif; ?>

                    </p>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php App()->getClientScript()->registerScript("ActivationFeedBackTriggers","
$('#activateTokenTable__selector--yes').on('click', function(e){ var run=function(){".$closedOnclickAction."}; run();});
$('#activateTokenTable__selector--no, #activateRedirectSurvey__selector').on('click', function(e){ var run=function(){".$noOnclickAction."}; run();});
",LSYii_ClientScript::POS_POSTSCRIPT); 
?>
