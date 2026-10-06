<?php
/**
 * "Organize columns" modal.
 *
 * @var $modalId string
 * @var $filterableColumns array
 * @var $filteredColumns array
 * @var $columnsData array
 * @var $ajaxUpdate string
 */

$orderedColumns = [];
foreach ($filteredColumns as $key) {
    if (isset($filterableColumns[$key])) {
        $orderedColumns[$key] = $filterableColumns[$key];
    }
}
foreach ($filterableColumns as $key => $column) {
    if (!isset($orderedColumns[$key])) {
        $orderedColumns[$key] = $column;
    }
}
?>

<div class="modal fade organize-columns-modal" id="<?= $modalId ?>" tabindex="-1" role="dialog" data-target="<?= CHtml::encode($ajaxUpdate) ?>" aria-labelledby="<?= $modalId ?>-label" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="pjax" method="POST" data-filtered-columns="<?= CHtml::encode(json_encode($filteredColumns)) ?>">
                <?php
                Yii::app()->getController()->renderPartial(
                    '/layouts/partial_modals/modal_header',
                    ['modalTitle' => gT('Organize columns'), 'modalTitleId' => $modalId . '-label']
                );
                ?>
                <div class="modal-body">
                    <p><?= gT('Select which columns you want to display inside this table. You can order the columns by drag and drop.') ?></p>
                    <div class="organize-columns-buttons mb-3">
                        <button type="button" id="<?= $modalId ?>-selectall" class="btn btn-outline-secondary">
                            <span class="ri-check-fill"></span>&nbsp;<?= gT('Select all') ?>
                        </button>
                        <button type="button" id="<?= $modalId ?>-clear" class="btn btn-outline-secondary">
                            <span class="ri-close-circle-line"></span>&nbsp;<?= gT('Clear selection') ?>
                        </button>
                    </div>
                    <div class="organize-columns-list">
                        <?php foreach ($columnsData as $column) : ?>
                            <?php if (!empty($column->header) && $column->name !== 'dropdown_actions' && !array_key_exists($column->name, $filterableColumns)) : ?>
                                <div class="organize-columns-item organize-columns-locked">
                                    <span class="organize-columns-handle ri-draggable" aria-hidden="true"></span>
                                    <label>
                                        <input type="checkbox" checked disabled>
                                        <?= $column->name === 'actions' ? gT('Actions') : $column->header ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div class="organize-columns-sortable">
                            <?php foreach ($orderedColumns as $key => $column) : ?>
                                <div class="organize-columns-item" data-column="<?= CHtml::encode($key) ?>">
                                    <span class="organize-columns-handle ri-draggable" aria-hidden="true"></span>
                                    <label>
                                        <input type="checkbox" value="<?= CHtml::encode($key) ?>" <?= in_array($key, $filteredColumns, true) ? 'checked' : '' ?>>
                                        <?= $column['modalLabel'] ?? $column['header'] ?>
                                    </label>
                                    <button type="button" class="btn btn-link btn-sm organize-columns-move organize-columns-up" aria-label="<?= CHtml::encode(gT('Move up')) ?>" title="<?= CHtml::encode(gT('Move up')) ?>">
                                        <span class="ri-arrow-up-s-line" aria-hidden="true"></span>
                                    </button>
                                    <button type="button" class="btn btn-link btn-sm organize-columns-move organize-columns-down" aria-label="<?= CHtml::encode(gT('Move down')) ?>" title="<?= CHtml::encode(gT('Move down')) ?>">
                                        <span class="ri-arrow-down-s-line" aria-hidden="true"></span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" name="<?= Yii::app()->request->csrfTokenName ?>" value="<?= CHtml::encode(App()->request->csrfToken) ?>"/>
                </div>
                <div class="modal-footer">
                    <button id="<?= $modalId ?>-cancel" type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?= gT('Cancel') ?></button>
                    <button type="submit" id="<?= $modalId ?>-submit" class="btn btn-primary" name="selectColumns" value="select">
                        <?= gT('Confirm selection') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
