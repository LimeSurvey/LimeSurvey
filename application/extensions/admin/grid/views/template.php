<?php

/**
 * @var $this CLSGridView
 * @var $massiveActionTemplate string
 */

$countSpan = "<span class='grid-selection-count'>0</span>"
?>

<div id="bottom-scroller" class="content-right scrolling-wrapper">
    {items}
</div>
<div class="grid-selection-bar"
     data-grid-id="<?= CHtml::encode($this->id) ?>"
     style="display:none;">
    <span class="grid-selection-count-text">
        <?= sprintf(gT('%s selected'), $countSpan) ?>
    </span>
    <span class="grid-selection-bar__divider" aria-hidden="true"></span>
    <button type="button" class="grid-selection-bar__deselect grid-deselect-all grid-selection-action">
        <?= gT('Deselect all') ?>
    </button>
    <button type="button" class="grid-selection-bar__close grid-deselect-all grid-selection-action" aria-label="<?= gT('Deselect all') ?>">
        <i class="ri-close-line"></i>
    </button>
</div>
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
