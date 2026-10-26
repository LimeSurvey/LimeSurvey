<?php
/**
 * TODO: this file seems to be unused
 *
 * @var array  $aAction
 * @var string $massiveModalDomId
 * @var string $massiveModalTitleId
 * @var string $massiveModalDialogSrId
 * @var string $showSelected
 * @var string $selectedUrl
 */
?>
<!-- Modal confirmation for <?php echo $aAction['action']; ?> -->
<div id="<?php echo CHtml::encode($massiveModalDomId); ?>"
     class="modal fade"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?php echo CHtml::encode($massiveModalTitleId . ' ' . $massiveModalDialogSrId); ?>"
     data-keepopen="<?php echo $aAction['keepopen']; ?>"
     data-show-selected="<?php echo $showSelected; ?>"
     data-selected-url="<?php echo $selectedUrl; ?>"
>
    <div class="modal-dialog modal-lg" role="document">
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
                <!-- shows list of selected items in the modal-->
                <div class="selected-items-list"></div>
                <?php if (isset($aAction['aCustomDatas'])) : ?>
                    <div class="custom-modal-datas d-none">
                        <?php foreach ($aAction['aCustomDatas'] as $aCustomData) : ?>
                            <input class="custom-data" type="hidden" name="<?php echo $aCustomData['name']; ?>" value="<?php echo $aCustomData['value']; ?>" />
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <a role="button" class="btn btn-primary btn-ok">
                    <span class="ri-check-fill"></span>
                    &nbsp;
                    <?php if (isset($aAction['yes'])) : ?>
                        <?php echo $aAction['yes']; ?>
                    <?php else : ?>
                        <?php eT("Yes"); ?>
                    <?php endif; ?>
                </a>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <span class="ri-forbid-2-line"></span>
                    &nbsp;
                    <?php if (isset($aAction['no'])) : ?>
                        <?php echo $aAction['no']; ?>
                    <?php else : ?>
                        <?php eT("No"); ?>
                    <?php endif; ?>
                </button>
            </div>
            <?php if ($aAction['keepopen'] == "yes") : ?>
                <div class="modal-footer modal-footer-close" style="display: none;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <span class="ri-forbid-2-line"></span>
                        &nbsp;
                        <?php eT("Close"); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
