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

<div class='side-body'>
    <h3><?php eT('Time statistics'); ?></h3>
    <script type='text/javascript'>
        var strdeleteconfirm = '<?php eT('Do you really want to delete this response?', 'js'); ?>';
        var strDeleteAllConfirm = '<?php eT('Do you really want to delete all marked responses?', 'js'); ?>';
    </script>
    <?php
    $this->widget('application.extensions.admin.grid.CLSGridView', [
            'dataProvider' => $model->search($surveyId, $language),
            'id' => 'time-grid',
            'emptyText' => gT('No surveys found.'),
            'ajaxUpdate' => 'time-grid',
            'lsAfterAjaxUpdate' => ['window.LS.doToolTip();'],
            'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                gT('%s rows per page'),
                CHtml::dropDownList(
                    'pageSize',
                    $pageSize,
                    Yii::app()->params['pageSizeOptions'],
                    ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                )
            ),
            'columns' => $columns
        ]);
    ?>

    <?php if ($statistics['count']) { ?>
        <div class="header ui-widget-header"><?php eT('Interview time'); ?></div>
        <table class="ls-statisticssummary">
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
