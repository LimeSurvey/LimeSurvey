<?php
/* @var $this SurveysGroupsController */
/* @var $dataProvider CActiveDataProvider */

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

    <h3><?php eT('Survey groups:'); ?></h3>

    <div class="row">
        <div class="col-sm-12 content-right">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $model->search(),
            ));
            ?>
        </div>
    </div>
</div>
