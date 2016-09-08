<div class="col-lg-12 list-surveys">
    <h3><?php eT("Share panel"); ?> </h3>
    <div class="row" style="margin-bottom: 100px">
        <div class="container-fluid">
            <div class="row">
                <?php
                  $this->widget('bootstrap.widgets.TbGridView', array(
                    'id' => 'share_central_participants',
                    'itemsCssClass' => 'table table-striped items',
                    'htmlOptions' => array('class'=> 'table-responsive'),
                    'dataProvider' => $model->search(),
                    'rowHtmlOptionsExpression' => '["data-participant_id" => $data->participant_id ]',
                    'columns' => $model->columns,
                    'filter'=>$model,
                    'ajaxType' => 'POST',
                    'afterAjaxUpdate' => 'bindButtons',
                    'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSizeShareParticipantView',
                                    $pageSizeShareParticipantView,
                                    Yii::app()->params['pageSizeOptions'],
                                    array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                                ),
                    ));
                ?>
            </div>
        </div>
    </div>
</div>

