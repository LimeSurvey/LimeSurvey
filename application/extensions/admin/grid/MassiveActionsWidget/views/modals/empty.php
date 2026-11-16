<?php
/**
 * @var array  $aAction
 * @var string $massiveModalDomId
 * @var string $massiveModalTitleId
 * @var string $massiveModalDialogSrId
 */
?>
<!-- Modal confirmation for <?php echo $aAction['action']; ?> -->
<div id="<?php echo CHtml::encode($massiveModalDomId); ?>"
     class="modal fade"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?php echo CHtml::encode($massiveModalTitleId . ' ' . $massiveModalDialogSrId); ?>"
>
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                [
                    'modalTitle'   => $aAction['sModalTitle'],
                    'modalTitleId' => $massiveModalTitleId,
                ]
            );
            ?>
            <div class="modal-body">
                <div class="modal-body-text"><?php echo $aAction['htmlModalBody']; ?></div>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <?php foreach ($aAction['htmlFooterButtons'] as $buttonHtml) : ?>
                    <?php echo $buttonHtml; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
