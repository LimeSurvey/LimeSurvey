<?php
/**
 * @var string $id              the modal id
 * @var string $modalTitle      the modal header title
 * @var string $body            the modal body html
 * @var string $footer          the modal footer html (empty hides the footer)
 * @var string $modalSize       extra class for .modal-dialog (e.g. 'modal-lg')
 * @var bool   $showCloseButton whether to show the header close (x) button
 * @var string $modalAttr       extra attributes for the modal wrapper (e.g. static backdrop)
 */
?>
<div id="<?= $id ?>" class="modal fade" tabindex="-1" role="dialog" <?= $modalAttr ?>>
    <div class="modal-dialog <?= $modalSize ?>" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $modalTitle ?></h5>
                <?php if ($showCloseButton) : ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php eT('Close'); ?>"></button>
                <?php endif; ?>
            </div>
            <div class="modal-body">
                <?= $body ?>
            </div>
            <?php if (!empty($footer)) : ?>
                <div class="modal-footer">
                    <?= $footer ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

