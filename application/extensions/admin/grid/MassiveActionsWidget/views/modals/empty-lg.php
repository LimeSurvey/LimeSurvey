<?php
$massiveModalId = 'massive-actions-modal-' . $this->gridid . '-' . $aAction['action'] . '-' . $key;
$massiveModalTitleId = $massiveModalId . '-title';
$massiveModalDialogSrId = $massiveModalTitleId . '-dialogsr';
?>
<!-- Modal confirmation for <?php echo $aAction['action'];?> -->
<div id="<?php echo CHtml::encode($massiveModalId); ?>" class="modal fade" role="dialog" aria-modal="true" aria-labelledby="<?php echo CHtml::encode($massiveModalTitleId . ' ' . $massiveModalDialogSrId); ?>">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-header">
                <h2 id="<?php echo CHtml::encode($massiveModalTitleId); ?>" class="modal-title h5"><?php echo CHtml::encode($aAction['sModalTitle']); ?></h2>
                <span class="visually-hidden" id="<?php echo CHtml::encode($massiveModalDialogSrId); ?>"><?php echo gT('Dialog'); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo gT('Close'); ?>"></button>
            </div>

            <div class="modal-body">
                <?php echo $aAction['htmlModalBody']; ?>
            </div>

            <div class="modal-footer modal-footer-buttons">
                <?php foreach ($aAction['htmlFooterButtons'] as $buttonHtml): ?>
                    <?php echo $buttonHtml; ?>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</div>
