<?php

/** @var bool $isOpenAccess */
/** @var int $surveyId */

?>

<div id="" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">

            <?php
            App()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Congrats! Your survey has been activated.')]
            );
            ?>

            <div class="modal-body" id="modal-body-activate">
                <div class="row">
                    <p><?php et('Want to share your survey right away?');?></p>
                    <a href="<?php App()->getController()->createUrl('/surveyAdministration/view', ['surveyid' => $surveyId])?>">
                        <?= gT('Sharing options ') ?><i class="ri-arrow-right-line"></i>
                    </a>
                </div>
                <div class="row">
                    <p><?php et('Statistics and responses are now accessible.');?></p>
                    <a href="<?php App()->getController()->createUrl('responses/browse', ['surveyid' => $surveyId])?>">
                        <?= gT('See all responses and statistics') ?><i class="ri-arrow-right-line"></i>
                    </a>
                </div>
                <div class="row">
                    <p><?php et('By default, surveys are activated in open-access mode and participants do not need an invitaion code.');?></p>
                </div>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php eT("Close"); ?></button>
            </div>

        </div>
    </div>
</div>
