<?php

/**
 * @var $this CLSGridView
 * @var $massiveActionTemplate string
 */
?>

<div id="bottom-scroller" class="content-right scrolling-wrapper">
    {items}
</div>
<div class="grid-selection-bar"
     data-grid-id="<?= CHtml::encode($this->id) ?>"
     data-label="<?= CHtml::encode(gT('rows selected')) ?>"
     style="display:none;">
    <span class="grid-selection-count"></span>
    <button type="button" class="btn btn-outline-g-700 btn-sm grid-deselect-all">
        <i class="ri-close-line"></i> <?= gT('Deselect all') ?>
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
