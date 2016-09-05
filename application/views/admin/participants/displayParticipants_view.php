<div class="col-xs-12">
    <h3 class="pagetitle row">
        <div class="col-xs-8 ">
            <?php eT("Central participant management"); ?>
        </div>
        <div class="col-xs-4 text-right">
            <button class="btn btn-default" id="addParticipantToCPP">
                <?php eT("Add new Participant"); ?>
                <i class="fa fa-plus-circle text-success"></i> 
            </button>
        </div>
    </h3>
<div class="row" style="margin-bottom: 100px">
  <div class="container-fluid">
    <div class="row">

    </div>
    <div class="row">
      <?php
        $this->widget('bootstrap.widgets.TbGridView', array(
            'id' => 'list_central_participants',
            'itemsCssClass' => 'table table-striped items',
            'dataProvider' => $model->search(),
            'columns' => $model->columns,
            'rowHtmlOptionsExpression' => '["data-participant_id" => $data->participant_id ]',
            'filter'=>$model,
            'htmlOptions' => array('class'=> 'table-responsive'),
            'itemsCssClass' => 'table table-responsive table-striped',
            'afterAjaxUpdate' => 'bindButtons',
            'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSizeParticipantView',
                $pageSizeParticipantView,
                Yii::app()->params['pageSizeOptions'],
                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
            ),
        ));
     ?>
    </div>
  </div>
</div>