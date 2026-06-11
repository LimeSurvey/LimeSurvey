<?php
/**
 * Error modal shown when the user triggers a massive action without selecting any item.
 * Rendered without $aAction or $key — builds its own IDs from $this->gridid.
 */
$firstSelectTitleId  = 'error-first-select-' . $this->gridid . '-title';
$firstSelectDialogSrId = $firstSelectTitleId . '-dialogsr';
?>
<!-- Modal error if no item selected -->
<div id="error-first-select<?php echo CHtml::encode($this->gridid); ?>"
     class="modal fade"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?php echo CHtml::encode($firstSelectTitleId . ' ' . $firstSelectDialogSrId); ?>"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                [
                    'modalTitle'   => gT('Error'),
                    'modalTitleId' => $firstSelectTitleId,
                ]
            );
            ?>
            <div class="modal-body">
                <?php eT('Please select at least one item'); ?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <?php eT("OK"); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- endof modal error if no item selected -->
