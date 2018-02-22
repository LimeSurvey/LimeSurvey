
<div class="row">
    <div class="col-sm-12 content-right">

        <?php $this->widget('bootstrap.widgets.TbGridView', array(
            'dataProvider' => $oSurveyTheme->search(),
            'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                CHtml::dropDownList(
                    'pageSize',
                    10,
                    Yii::app()->params['pageSizeOptions'],
                    array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
                )
            ),            
            'columns' => array(
                array(
                    'header' => gT('Preview'),
                    'name' => 'preview',
                    'value'=> '$data->preview',
                    'type'=>'raw',
                    'htmlOptions' => array('class' => 'col-md-1'),
                ),

                array(
                    'header' => gT('Name'),
                    'name' => 'template_name',
                    'value'=>'$data->template_name',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),

                array(
                    'header' => gT('Description'),
                    'name' => 'template_name',
                    'value'=>'$data->template->description',
                    'htmlOptions' => array('class' => 'col-md-3'),
                    'type'=>'raw',
                ),

                array(
                    'header' => gT('Type'),
                    'name' => 'templates_type',
                    'value'=>'$data->typeIcon',
                    'type' => 'raw',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),

                array(
                    'header' => gT('Extends'),
                    'name' => 'templates_extends',
                    'value'=>'$data->template->extends',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),

                array(
                    'header' => '',
                    'name' => 'actions',
                    'value'=>'$data->buttons',
                    'type'=>'raw',
                    'htmlOptions' => array('class' => 'col-md-1'),
                ),

            )));
        ?>

    </div>
</div>
