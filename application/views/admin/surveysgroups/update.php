<?php
/* @var $this SurveysGroupsController */
/* @var $model SurveysGroups */
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

    <h3><?php eT('Update survey group: '); echo '<strong><em>'.$model->title.'</strong></em>'; ?></h3>

    <?php $this->renderPartial('./surveysgroups/_form', array('model'=>$model)); ?>

    <div class="col-sm-12 list-surveys">
        <h2><?php eT('Surveys in this group:'); ?></h2>
        <?php
            $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                        'model'            => $oSurveySearch,
                        'bRenderSearchBox' => false,
                    ));
        ?>
    </div>

</div>
