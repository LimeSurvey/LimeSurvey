<?php

/**
 * @var $this CLSGridView
 * @var $massiveActionTemplate string
 * @var $showSelectionBar bool
 */

$countSpan = "<span class='grid-selection-count'>0</span>";
?>

<div id="bottom-scroller" class="content-right scrolling-wrapper">
    {items}
</div>
<?php if ($showSelectionBar ?? true): ?>
<div class="grid-selection-bar"
     data-grid-id="<?= CHtml::encode($this->id) ?>"
     <?php if ($this->lsSelectAllEnabled) : ?>
     data-total-count="<?= (int) $this->dataProvider->getTotalItemCount() ?>"
    data-select-all-disable-threshold="<?= (int) $this->lsSelectAllDisableThreshold ?>"
    data-select-all-warning-message="<?= CHtml::encode(gT('Individual deselection is unavailable when more than {threshold} entries are selected. Reduce the selection below {threshold} to enable this feature.')) ?>"
     <?php endif; ?>
     style="display:none;">
    <span class="grid-selection-count-text">
        <?= sprintf(gT('%s selected'), $countSpan) ?>
    </span>
    <span class="grid-selection-bar__divider" aria-hidden="true"></span>
    <?php if ($this->lsSelectAllEnabled) : ?>
    <button type="button" class="grid-selection-bar__select-all grid-select-all grid-selection-action">
        <?= gT('Select all') ?>
    </button>
    <?php endif; ?>
    <button type="button" class="grid-selection-bar__deselect grid-deselect-all grid-selection-action">
        <?= gT('Deselect all') ?>
    </button>
    <button type="button" class="grid-selection-bar__close grid-deselect-all grid-selection-action" aria-label="<?= gT('Deselect all') ?>">
        <i class="ri-close-line"></i>
    </button>
</div>
<?php endif; ?>
    <div class="grid-view-ls-footer">
            <div class="massive-action-container" id="massive-action-container">
                <?= $massiveActionTemplate ?>
            </div>
            <div class="pagination-container">{pager}</div>
            <div class="summary-container">{summary}</div>
    </div>
<?php
if (!empty($this->lsAdditionalColumns)) {
    App()->getController()->widget('ext.admin.grid.ColumnFilterWidget.ColumnFilterWidget', [
        'modalId'           => 'column-filter-modal',
        'filterableColumns' => $this->lsAdditionalColumns,
        'filteredColumns'   => $this->lsAdditionalColumnsSelected,
        'columnsData'       => $this->columns,
        'ajaxUpdate'        => $this->id,
    ]);
}
?>
