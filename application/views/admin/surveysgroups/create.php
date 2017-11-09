<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createSurveyGroups');

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

    <h3><?php eT('Create survey groups:').$model->title; ?></h3>

    <?php $this->renderPartial('./surveysgroups/_form', array('model'=>$model)); ?>

</div>    
