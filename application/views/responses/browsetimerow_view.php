<?php
/**
 * @var $model SurveyTimingDynamic
 * @var $surveyId int
 * @var $language string
 * @var $pageSize int
 * @var $columns array
 * @var $statistics array
 */
?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3><?php eT('Time statistics'); ?></h3>
    <script type='text/javascript'>
        var strdeleteconfirm = '<?php eT('Do you really want to delete this response?', 'js'); ?>';
        var strDeleteAllConfirm = '<?php eT('Do you really want to delete all marked responses?', 'js'); ?>';
    </script>
    <?php
    $this->widget(
        'bootstrap.widgets.TbGridView',
        [
            'dataProvider'    => $model->search($surveyId, $language),
            'id'              => 'time-grid',
            'emptyText'       => gT('No surveys found.'),
            'htmlOptions'     => ['class' => 'table-responsive grid-view-ls time-statistics-table'],
            'ajaxUpdate'      => 'time-grid',
            'afterAjaxUpdate' => 'window.LS.doToolTip',
            'template'        => "{items}\n<div id='timeListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
            'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                    )
                ),

            'columns' => array_merge(
                [
                    [
                        'header'      => '',
                        'name'        => 'actions',
                        'value'       => '$data->buttons',
                        'type'        => 'raw',
                        'htmlOptions' => ['class' => 'time-statistics-row-buttons'],
                    ]
                ],
                $columns
            )
        ]
    );
    ?>

    <?php if ($statistics['count']) { ?>
        <div class="header ui-widget-header"><?php eT('Interview time'); ?></div>
        <table class="statisticssummary">
            <tr>
                <th><?php eT('Average interview time:'); ?></th>
                <td title=""><?php printf(gT("%s min. %s sec."), $statistics['avgmin'], $statistics['avgsec']) ?></td>
            </tr>
            <tr>
                <th><?php eT('Median:'); ?></th>
                <td><?php printf(gT("%s min. %s sec."), $statistics['allmin'], $statistics['allsec']) ?> </td>
            </tr>
        </table>
    <?php } ?>

</div>

<script type="text/javascript">
    jQuery(function ($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function () {
            $.fn.yiiGridView.update('time-grid', {data: {pageSize: $(this).val()}});
        });
    });
</script>
