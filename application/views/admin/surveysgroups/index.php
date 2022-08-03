<?php
// TODO: File seems unused, see listSurveys_view.php (PT)
/* @var $this SurveysGroupsController */
/* @var $dataProvider CActiveDataProvider */

?>
<div class="col-12 list-surveys">

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
        <div class="col-12 content-right">
            <?php
            $this->widget('yiistrap_fork.widgets.TbGridView', array(
                'dataProvider' => $model->search(),
            ));
            ?>
        </div>
    </div>
</div>
