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
                ['modalTitle' => gT('Activate survey')]
            );
            ?>

            <div class="modal-body" id="modal-body-activate">
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>

                <button id="saveactivateBtn" type="button" class="btn btn-primary" onclick="activateWithOptions()">
                    <?php eT("Save and activate"); ?>
                </button>
            </div>

        </div>
    </div>
</div>
