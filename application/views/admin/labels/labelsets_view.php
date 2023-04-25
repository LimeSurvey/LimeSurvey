<?php
/**
 * This file render the list of label sets
 *
 * @var $this AdminController
 * @var LabelSet $model the LabelSets model
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('viewLabelSets');

?>
<?php $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']); ?>
<div class="row">
    <div class="col-12 content-right">
        <?php
        $this->widget(
            'application.extensions.admin.grid.CLSGridView',
            [
                'dataProvider' => $model->search(),
                // Number of row per page selection
                'id' => 'labelsets-grid',
                'emptyText' => gT('No label sets found.'),
                'ajaxUpdate' => 'labelsets-grid',
                'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
                'columns' => [
                    [
                        'header' => gT('Label set ID'),
                        'name' => 'labelset_id',
                        'value' => '$data->lid',
                        'htmlOptions' => ['class' => ''],
                    ],
                    [
                        'header' => gT('Name'),
                        'name' => 'name',
                        'value' => '$data->label_name',
                        'htmlOptions' => ['class' => ''],
                    ],
                    [
                        'header' => gT('Languages'),
                        'name' => 'languages',
                        'value' => '$data->languages',
                        'type' => 'LanguageList',
                        'htmlOptions' => ['class' => ''],
                    ],
                    [
                        'header'            => gT('Action'),
                        'name'              => 'actions',
                        'type'              => 'raw',
                        'value'             => '$data->buttons',
                        'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                        'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                        'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
                    ],

                ],

            ]
        );
        ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function () {
            $.fn.yiiGridView.update('labelsets-grid', {data: {pageSize: $(this).val()}});
        });
        //Delete button
        $(document).ready(function () {
            $('a[data-confirm]').click(function () {
                return confirm($(this).attr('data-confirm'));
            });
        });
    });
</script>
