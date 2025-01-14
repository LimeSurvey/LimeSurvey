<?php
/**
 * Subview: Error messsage in the usermanagement panel
 *
 * @package UserManagement
 * @author GititSurvey GmbH <info@gitit-tech.com>
 * @license GPL3.0
 */
?>
<div class="modal-header">
    <h5 class="modal-title"><?= gT('Error') ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row selector--animated_row">
        <div class="col-12 text-center">
            <div class="cross_mark">
                <div class="sa-icon sa-error animate">
                    <span class="sa-line sa-tip animateerrorTip"></span>
                    <span class="sa-line sa-long animateerrorLong"></span>
                    <div class="sa-placeholder"></div>
                    <div class="sa-fix"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row ls-space margin top-15 bottom-15">
        <?php foreach ($errors as $error) {
            echo "<pre>" . print_r($error, true) . "</pre>";
        }
        ?>
    </div>
</div>
<div class="modal-footer">
    <?php if (!isset($noButton)): ?>
        <button id="exitForm" class="btn btn-cancel" data-bs-dismiss="modal">
            <?= gT('Close') ?>
        </button>
    <?php endif; ?>
</div>
