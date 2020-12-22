<div class="col-lg-12 list-surveys">
    <?php
    $this->renderPartial(
        '/admin/super/fullpagebar_view',
        array(
            'fullpagebar' => $buttons,
        )
    ); ?>
    <h1 class="pagetitle h2"><?php eT('Permission for group: '); echo '<strong><em>'.CHtml::encode($model->title).'</strong></em>'; ?></h1>
    <?php
        $this->renderPartial('/SurveysGroupsPermission/subviews/'.$subview,$aPermissionData);
    ?>
</div>
