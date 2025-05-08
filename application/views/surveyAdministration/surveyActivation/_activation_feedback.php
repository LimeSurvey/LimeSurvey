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
<div class='side-body'>
    <div class="row welcome survey-action">
        <div class="col-12 content-right">
            <div class="card card-primary h-100">
                <h3>
                    <?php eT('Activate Survey'); ?> (<?php echo $survey->currentLanguageSettings->surveyls_title; ?>)
                </h3>
                <p class='lead'>
                    <?php eT("Your survey has been activated and the responses and statistics section is now available."); ?>
                </p>

                    <?php if ($warning) : ?>
                        <strong class='text-danger'>
                            <?php eT("The required directory for saving the uploaded files couldn't be created. Please check file premissions on the /upload/surveys directory."); ?>
                        </strong>
                    <?php endif; ?>

                    <?php if ($allowregister && !tableExists('tokens_' . $iSurveyID)) : ?>
                        <p>
                            <?php eT("This survey allows public registration. A survey participants table must also be created."); ?>
                            <br/>
                            <br/>
                            <input
                                type="submit"
                                class="btn btn-outline-secondary btn-lg limebutton"
                                value="<?php eT("Initialize participant list"); ?>"
                                onclick='<?php echo $onclickAction; ?>'
                            />
                        </p>
                    <?php else : ?>
                        <p>
                            <br/>
                            <?php if (!tableExists('tokens_' . $iSurveyID)) : ?>
                                <!-- Open Access Mode -->
                                <?php eT("By default, surveys are activated in open-access mode. In this mode participants do not need an invitation (access code) to complete the survey."); ?>
                                <?php eT("You can share your survey via URL, QR code or social media. Navigate to Settings --> Overview --> Share survey."); ?>
                                <br/>
                                <br/>
                                <!-- Closed Access Mode -->
                                <?php eT("In closed-access mode, only those who are invited (and have an access code) can access the survey. You can activate the closed access mode now or later."); ?>
                                <br/>
                                <br/>
                                <?php eT("You can switch back to open-access mode at any time. Navigate to Settings -> Survey participants and click on the red 'Delete participants table' button in the top bar."); ?>
                                <br/>
                                <br/>
                                <input
                                    type='submit'
                                    class='btn btn-outline-secondary'
                                    id='activateTokenTable__selector--yes'
                                    value='<?php eT("Switch to closed-access mode"); ?>'
                                />
                                <input
                                    type='submit'
                                    class='btn btn-outline-secondary'
                                    id='activateTokenTable__selector--no'
                                    value='<?php eT("Continue in open-access mode"); ?>'
                                />
                            <?php else : ?>
                                <input
                                    type='submit'
                                    class='btn btn-outline-secondary'
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
</div>

<?php App()->getClientScript()->registerScript("ActivationFeedBackTriggers", "
$('#activateTokenTable__selector--yes').on('click', function(e){ var run=function(){" . $closedOnclickAction . "}; run();});
$('#activateTokenTable__selector--no, #activateRedirectSurvey__selector').on('click', function(e){ var run=function(){" . $noOnclickAction . "}; run();});
", LSYii_ClientScript::POS_POSTSCRIPT);
?>
