<?php
/* @var $this TemplateOptionsController */
/* @var $dataProvider CActiveDataProvider */

// TODO: rename to template_list.php

?>
<div class="col-lg-12 list-surveys">

    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'returnbutton'=>array(
                'url'=>'index',
                'text'=>gT('Close'),
            ),
        )
    )); ?>

    <h3><?php eT('Available templates:'); ?></h3>
    <div class="row">
        <div class="col-sm-12 content-right">
            <?php $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $model->search(),
                'columns' => array(
                    array(
                        'header' => gT('Preview'),
                        'name' => 'preview',
                        'value'=> '$data->preview',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),

                    array(
                        'header' => gT('name'),
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
                        'header' => gT('type'),
                        'name' => 'templates_type',
                        'value'=>'$data->typeIcon',
                        'type' => 'raw',
                        'htmlOptions' => array('class' => 'col-md-2'),
                    ),

                    array(
                        'header' => gT('extends'),
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
</div>
