<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
?>

<div class="col-lg-12 list-surveys">

    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'returnbutton'=>array(
                'url'=>'admin/survey/sa/listsurveys#surveygroups',
                'text'=>gT('Close'),
            ),
        )
    )); ?>

    <h3><?php eT('Create SurveysGroups:').$model->title; ?></h3>

    <?php $this->renderPartial('./surveysgroups/_form', array('model'=>$model)); ?>

</div>    
