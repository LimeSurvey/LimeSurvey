<?php

/**
 * @var $model Surveymenu
 */
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
?>

<div class="ls-flex-column">
    <div class="col-12 h1"><?php eT('Survey menu') ?></div>
    <div class="ls-flex-row">
        <div class="col-12 ls-flex-item">
            <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'dataProvider'  => $model->search(),
                    'id'            => 'surveymenu-shortlist-grid',
                    'columns'       => $model->getShortListColumns(),
                    'emptyText'     => gT('No customizable entries found.'),
                    'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'surveymenushortlistPageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
                   'ajaxUpdate' => 'surveymenu-shortlist-grid'
                ]
            );
            ?>
        </div>
    </div>
</div>

<!-- update rows with pagination -->
<script type="text/javascript">
    jQuery(function ($) {
        $(document).on("change", '#surveymenushortlistPageSize', function () {
            $.fn.yiiGridView.update('surveymenu-shortlist-grid', {data: {pageSize: $(this).val()}});
        });
    });
</script>

