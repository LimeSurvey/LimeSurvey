<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Saved successfully')]
);
?>
<div class="modal-body">
    <div class="row selector--animated_row">
        <div class="col-12 text-center">
            <div class="check_mark">
                <div class="sa-icon sa-success animate">
                    <span class="sa-line sa-tip animateSuccessTip"></span>
                    <span class="sa-line sa-long animateSuccessLong"></span>
                    <div class="sa-placeholder"></div>
                    <div class="sa-fix"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <?php if (isset($sMessage)): ?>
                <?= $sMessage ?>
            <?php endif; ?>
            <?php if (isset($sDebug) && Yii::app()->getConfig('debug') > 0): ?>
                <?= $sDebug ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?php if (!isset($noButton)): ?>
        <button id="exitForm" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= gT('Close') ?></button>
    <?php endif; ?>
</div>
