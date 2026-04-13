<?php

/**
 * @var $model SurveymenuEntries
 */
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
?>
<div class="ls-flex-column">
    <div class="col-12 h1"><?php eT('Menu entries') ?></div>
    <div class="ls-flex-row">
        <div class="col-12 ls-flex-item">
            <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'dataProvider' => $model->search(),
                    'id'           => 'surveymenu-entries-shortlist-grid',
                    'columns'      => $model->getShortListColumns(),
                    'emptyText'    => gT('No customizable entries found.'),
                    'summaryText'  => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'surveymenuentriesshortlistPageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
                    'ajaxUpdate' => 'surveymenu-entries-shortlist-grid'
                ]
            );
            ?>
        </div>
    </div>
</div>

<!-- update rows with pagination -->
<script type="text/javascript">
    jQuery(function ($) {
        $(document).on("change", '#surveymenuentriesshortlistPageSize', function () {
            $.fn.yiiGridView.update('surveymenu-entries-shortlist-grid', {data: {pageSize: $(this).val()}});
        });
    });
</script>


