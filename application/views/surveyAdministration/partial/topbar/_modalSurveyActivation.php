<?php
   /** @var Survey $oSurvey */
   /** @var bool|array $failedcheck */
   /** @var bool|array $failedgroupcheck */
   /** @var array $aSurveysettings */
?>

<div id="surveyactivation-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">

<?php
  //modal header
App()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gt('Activate survey')]
);
?>

<?php
 //modal content
?>
<div class="modal-body" id="modal-body-activate">
    <?php

/*
    if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck)) {
        //survey can not be activated
        App()->getController()->renderPartial('/surveyAdministration/_activateSurveyCheckFailed', [
            'failedcheck' => $failedcheck,
            'failedgroupcheck' => $failedgroupcheck,
            'surveyid' => $oSurvey->sid
        ]);
    } else {
        App()->getController()->renderPartial('/surveyAdministration/_activateSurveyOptions', [
            'aSurveysettings' => $aSurveysettings,
            'oSurvey' => $oSurvey
        ]);
    } */

    ?>
</div>

<?php
  //modal footer
?>
            <div class="modal-footer modal-footer-yes-no">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <a id="actionBtn" class="btn btn-ok" data-actionbtntext="<?php eT('Save and Activate'); ?>"></a>
            </div>

        </div>
    </div>
</div>
