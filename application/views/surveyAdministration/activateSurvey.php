<?php

/** @var Survey $oSurvey */
/** @var bool|array $failedcheck */
/** @var bool|array $failedgroupcheck */
/** @var array $aSurveysettings */

//todo: this view is deprecated, we moved content to a modal

?>
<div class='side-body'>
    <h3>
        <?php eT('Activate survey'); ?> (<?php echo $oSurvey->currentLanguageSettings->surveyls_title; ?>)
    </h3>
    <div class="row">
        <div class="col-12 col-xl-10">
            <div class="card card-primary h-100">
                <?php
                if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck)) {
                    //survey can not be activated
                    $this->renderPartial('_activateSurveyCheckFailed', [
                        'failedcheck' => $failedcheck,
                        'failedgroupcheck' => $failedgroupcheck,
                        'surveyid' => $oSurvey->sid
                    ]);
                } else {
                    $this->renderPartial('_activateSurveyOptions', [
                        'aSurveysettings' => $aSurveysettings,
                        'oSurvey' => $oSurvey
                    ]);
                }
                ?>
            </div>
        </div>
    </div>
</div>
