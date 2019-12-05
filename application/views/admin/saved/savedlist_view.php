<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3>
        <?php eT('Saved responses'); ?>
        <small><?php echo flattenText($sSurveyName) . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?></small>
    </h3>

        <div class="row">
            <div class="col-lg-12 content-right">
<?php
    $this->widget('bootstrap.widgets.TbGridView', array(
            'id' => 'saved-grid',
            'ajaxUpdate'    => 'saved-grid',
            'dataProvider'  => $model->search(),
            'columns'       => $model->columns,
            'filter'        => $model,
            'ajaxType'      => 'POST',
            'template'      => "{items}\n<div class='row'><div class='col-sm-4 col-md-offset-4'>{pager}</div><div class='col-sm-4'>{summary}</div></div>",
            'emptyText'=>gT('No customizable entries found.'),
            'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                CHtml::dropDownList(
                    'savedResponsesPageSize',
                    $savedResponsesPageSize,
                    Yii::app()->params['pageSizeOptions'],
                    array(
                        'class'=>'changePageSize form-control', 
                        'style'=>'display: inline; width: auto',
                        'onchange' => "$.fn.yiiGridView.update('saved-grid',{ data:{ savedResponsesPageSize: $(this).val() }});"
                    )
                )
            ),
        )
    );
?>
            </div>
        </div>
</div>
