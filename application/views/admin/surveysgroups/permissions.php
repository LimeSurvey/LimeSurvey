<div class="col-lg-12 list-surveys">
    <?php $this->renderPartial('super/fullpagebar_view', array(
        'fullpagebar' => array(
            'returnbutton'=>array(
                'url'=>'admin/survey/sa/listsurveys#surveygroups',
                'text'=>gT('Close'),
            ),
        )
    )); ?>
    <h3><?php eT('Permission for group: '); echo '<strong><em>'.CHtml::encode($model->title).'</strong></em>'; ?></h3>
    <div class="alert alert-warning">This is work in prgress</div>
</div>
