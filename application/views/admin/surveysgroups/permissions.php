<div class="col-lg-12 list-surveys">
    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'returnbutton'=>array(
                'url'=>'admin/survey/sa/listsurveys#surveygroups',
                'text'=>gT('Close'),
            ),
        )
    )); ?>
    <h1 class="pagetitle h2"><?php eT('Permission for group: '); echo '<strong><em>'.CHtml::encode($model->title).'</strong></em>'; ?></h1>
    <?php
        $this->renderPartial('surveysgroups/permission/'.$subview,$aPermissionData);
    ?>
</div>
