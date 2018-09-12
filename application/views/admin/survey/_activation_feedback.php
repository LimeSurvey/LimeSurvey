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
* @var $noOnclickAction            convertGETtoPOST(Yii::app()->getController()->createUrl("admin/survey/sa/view/surveyid/".$iSurveyID))
*
*/
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-lg-12 content-right">
            <div class='jumbotron message-box'>
                <h3><?php eT("Activate Survey");?>(<?php echo $iSurveyID; ?>)</h3>
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
                        <?php eT("This survey allows public registration. A token table must also be created."); ?>
                        <br />
                        <br />
                        <input
                            type="submit"
                            value="<?php eT("Initialise participant table"); ?>"
                            onclick="<?php echo $onclickAction;?>"
                            />
                    </p>
                    <?php else:?>
                    <p>
                        <?php eT("This survey is now active, and responses can be recorded."); ?>
                        <br />
                        <br />
                        <?php if(!tableExists('tokens_'.$iSurveyID)):?>

                            <strong><?php eT("Open-access mode:");?></strong>
                            <?php eT("No invitation code is needed to complete the survey."); ?>
                            <br />
                            <?php eT("You can switch to the closed-access mode by initialising a token table with the button below."); ?>
                            <br />
                            <br />
                            <input
                                type='submit'
                                class='btn btn-default'
                                value='<?php eT("Switch to closed-access mode"); ?>'
                                onclick="<?php echo $closedOnclickAction;?>"
                                />
                            <input
                                type='submit'
                                class='btn btn-default'
                                value='<?php eT("No, thanks."); ?>'
                                onclick="<?php echo $noOnclickAction;?>"
                                />
                            <?php else:?>
                            <input
                                type='submit'
                                class='btn btn-default'
                                value='<?php eT("Back to survey home"); ?>'
                                onclick="<?php echo $noOnclickAction;?>"
                                />

                            <?php endif; ?>

                    </p>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>
