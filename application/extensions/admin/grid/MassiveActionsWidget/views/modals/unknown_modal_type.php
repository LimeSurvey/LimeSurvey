<?php
/**
 * Fallback error modal rendered when an unrecognised modalType is encountered.
 *
 * @var array  $aAction
 * @var string $massiveModalDomId
 * @var string $massiveModalTitleId
 * @var string $massiveModalDialogSrId
 */
?>
<!-- Modal for unknown modal type error -->
<div id="<?php echo CHtml::encode($massiveModalDomId); ?>"
     class="modal fade"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?php echo CHtml::encode($massiveModalTitleId . ' ' . $massiveModalDialogSrId); ?>"
     data-keepopen="<?php echo $aAction['keepopen']; ?>"
>
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                [
                    'modalTitle'   => gT('Error'),
                    'modalTitleId' => $massiveModalTitleId,
                ]
            );
            ?>
            <div class="modal-body">
                <?php eT('Unknown modal view type in Massive Actions Widget!'); ?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <span class="ri-forbid-2-line"></span>
                    &nbsp;
                    <?php eT("Close"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
