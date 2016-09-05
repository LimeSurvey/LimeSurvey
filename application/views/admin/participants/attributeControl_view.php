<div class="col-xs-12 ">
    <h3 class="pagetitle row">
        <div class="col-xs-8 ">
            <?php eT("Attribute management"); ?>
        </div>
        <div class="col-xs-4 text-right">
            <button class="btn btn-default" id="addParticipantAttributeName">
                <?php eT("Add new Attribute"); ?>
                <i class="fa fa-plus-circle text-success"></i> 
            </button>
        </div>
    </h3>

    <div class="row">
        <div class="container-fluid">
        <div class="row">

        </div>
        <div class="row">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'id' => 'list_attributes',
                'itemsCssClass' => 'table table-striped items',
                'dataProvider' => $model->search(),
                'columns' => $model->columns,
                'filter'=>$model,
                'htmlOptions' => array('class'=> 'table-responsive'),
                'rowHtmlOptionsExpression' => '["data-attribute_id" => $data->attribute_id]',
                'itemsCssClass' => 'table table-responsive table-striped',
                'afterAjaxUpdate' => 'bindButtons',
                'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSizeAttributes',
                                $pageSizeAttributes,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                            ),
                    ));
                ?>
            </div>
    </div>

            <div id="pager">

            </div>
        </div>
    </div>
</div>
