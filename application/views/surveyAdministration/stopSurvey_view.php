<?php

/** @var int $surveyid */
/** @var string $dbprefix */
/** @var string $date */
/** @var string $sNewSurveyTableName */

?>
<div class='side-body stop-survey-body'>
    <div class="row">
        <div class="col-6 content-right">
            <h2>
                <?php eT("You want to stop your survey");
                echo " <em>($surveyid)</em> ?"; ?>
            </h2>
            <p>
                <?php eT("There are two ways to stop a survey. Please decide below:"); ?>
            </p>
            <div class="help-block">
                <?php
                App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("Attention: Please read the following carefully before stopping your survey."),
                    'type' => 'warning',
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-6 col-xl-5">
            <div class="card card-primary h-100">
                <div class="card-header ">
                    <i class="ri-skip-forward-fill fs-4"></i>
                    <h5 class="card-title"><?php eT("Expiration"); ?></h5>
                </div>
                <div class="card-body px-0 d-flex">
                    <ul>
                        <li><?php printf(gT("Responses & participant information %swill be kept.%s"), '<b>', '</b>'); ?></li>
                        <li><?php printf(gT("%sCannot%s be %saccessed%s by %sparticipants%s anymore."), '<b>', '</b>', '<b>', '</b>', '<b>', '</b>'); ?></li>
                        <li><?php printf(gT("The %sability%s to %schange questions%s, groups and settings is %slimited%s. A message will be displayed stating that the survey has expired."),
                                '<b>', '</b>', '<b>', '</b>', '<b>', '</b>'); ?></li>
                        <li><?php eT("It is still possible to perform statistical analysis on responses."); ?></li>
                    </ul>
                </div>
                <div class="card-footer d-flex p-0">
                    <?php echo CHtml::form(array("surveyAdministration/expire/surveyid/{$surveyid}/"), 'post'); ?>
                    <p class="my-0">
                        <input
                            class="btn btn-outline-secondary px-4"
                            type='submit'
                            value='<?php eT("Expire survey"); ?>'
                        />
                    </p>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-5">
            <div class="card card-primary h-100">
                <div class="card-header ">
                    <i class="ri-stop-fill text-danger fs-4"></i>
                    <h5 class="card-title text-danger"><?php eT("Deactivation"); ?></h5>
                </div>
                <div class="card-body px-0  d-flex">
                    <ul>
                        <li><?php printf(gT('Responses are %sno longer accessible.%s Your response table will be renamed to: %s%s%s'),
                                '<b>', '</b><br>','<b>', $sNewSurveyTableName,'</b>'); ?></li>
                        <li><?php printf(gT("All %sparticipant information will be lost%s."), '<b>', '</b>'); ?></li>
                        <li><?php printf(gT("%sCannot%s be %saccessed%s by %sparticipants%s. A message will be displayed stating that the survey has been closed."),
                                '<b>', '</b>','<b>', '</b>', '<b>', '</b>'); ?></li>
                        <li><?php printf(
                            gT(
                            "%sQuestions%s, %sgroups%s and %ssettings%s can be %sedited%s again."),
                                '<b>', '</b>','<b>', '</b>', '<b>', '</b>', '<b>', '</b>'
                            ); ?></li>
                        <p class="mt-4">
                            <?php et('Important: Export your responses before deactivating your survey.') ?>
                            <a href='<?php echo $this->createUrl('admin/export/sa/exportresults/surveyid/' . $surveyid) ?>'>
                                <?php eT("See details.") ?>
                            </a>
                        </p>
                    </ul>
                </div>
                <div class="card-footer d-flex p-0">
                    <?php echo CHtml::form(array("surveyAdministration/deactivate/surveyid/{$surveyid}/"), 'post'); ?>
                    <p class="my-0">
                        <input
                            class="btn btn-outline-secondary px-4"
                            type='submit'
                            value='<?php eT("Deactivate survey"); ?>'
                        />
                    </p>
                    <input type='hidden' value='Y' name='ok'/>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-xl-10 mt-6 text-center">
        <a href="<?php echo Yii::app()->createUrl('surveyAdministration/view/surveyid/' . $surveyid); ?>">
            <i class="ri-arrow-left-line "></i>
            <?php eT("I don't want to stop my survey right now."); ?>
        </a>
    </div>
    </div>
</div>
